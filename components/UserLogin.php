<?php namespace DMA\Friends\Components;

use Cms\Classes\ComponentBase;
use Redirect;
use Validator;
use October\Rain\Support\ValidationException;
use RainLab\User\Models\Settings as UserSettings;
use RainLab\User\Models\User;
use DMA\Friends\Classes\UserExtend;
use DMA\Friends\Models\Usermeta;
use DMA\Friends\Wordpress\Auth as WordpressAuth;
use Cms\Classes\Theme;
use System\Classes\SystemException;
use Cms\Classes\Page;
use File;
use Lang;
use Auth;
use Flash;
use Event;

class UserLogin extends ComponentBase
{

    use \System\Traits\ViewMaker;

    public function componentDetails()
    {
        return [
            'name'        => 'User Registration and Login form',
            'description' => 'Provides login and user registration forms'
        ];
    }

    public function defineProperties()
    {
        return [
            'redirect' => [
                'title'       => 'rainlab.user::lang.account.redirect_to',
                'description' => 'rainlab.user::lang.account.redirect_to_desc',
                'type'        => 'dropdown',
                'default'     => ''
            ],
        ];
    }

    public function getRedirectOptions()
    {
        return [ '' => '- none -' ] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->loadAssets();
    }

    /**
     * Render the login form.  Override the partial "login-form" 
     * in the active theme to customize the login form
     */
    public function onLogin()
    {        
        return $this->renderPartial('@modalDisplay', [
            'title'     => Lang::get('dma.friends::lang.userLogin.loginTitle'),
            'content'   => $this->makePartial('login-form'),
        ]);
    }

    /**
     * Submit handler for the login form
     */
    public function onUserLogin()
    {
        try{
            
            // Update wordpress passwords if necessary
            WordpressAuth::verifyFromEmail(post('email'), post('password'));
      
            /*  
             * Validate input
             */
            $data = post();
            $rules = [ 
                'password' => 'required|min:2'
            ];  
    
            $loginAttribute = UserSettings::get('login_attribute', UserSettings::LOGIN_EMAIL);
    
            if ($loginAttribute == UserSettings::LOGIN_USERNAME)
                $rules['login'] = 'required|between:2,64';
            else
                $rules['login'] = 'required|email|between:2,64';
    
            if (!in_array('login', $data))
                $data['login'] = post('username', post('email'));

            /*
             * Validate user credentials
             */
            $validation = Validator::make($data, $rules);
            if ($validation->fails())
                throw new ValidationException($validation);

        
            /*  
             * Authenticate user
             */
            $user = Auth::authenticate([
                'login' => array_get($data, 'login'),
                'password' => array_get($data, 'password')
            ], true);
    
            /*
             * Fire event that user has logged in
             */
            Event::fire('auth.login', $user);
    
            /*  
             * Redirect to the intended page after successful sign in
             */
            $redirectUrl = $this->pageUrl($this->property('redirect'));
    
            if ($redirectUrl = post('redirect', $redirectUrl))
                return Redirect::intended($redirectUrl);
        
        
        } catch(\Exception $e) {
            // Catch all exceptions producced by RainLab User or DMA authentication
            // and update error block message using OctoberCMS Ajax framework
            $message = Lang::get('dma.friends::lang.userLogin.failCredentials');
                       
            // Bit doggy but if the exception message contains the login
            // is because the account is been suspend or banned by RainLab user plugin
            // This usually because the user has atent to loging multiple times with a 
            // wrong password.
            if(preg_match("/\[" . $data['login'] . "\]/", $e->getMessage())){ 
                $message = $message = Lang::get('dma.friends::lang.userLogin.throttleUser', $data);
            }
            
            // Send to log the authentication exception.
            \Log::debug($e);
            
            return [
               '.modal-content #errorBlock' => $message
            ];
      
        }
        
    }
    
    /**
     * Render the registration form.  Override the partial "register-form"
     * in the active theme to customize the registration form
     */
    public function onForgotPassword()
    {    
        return $this->renderPartial('@modalDisplay', [
            'title'     => Lang::get('dma.friends::lang.userLogin.forgotPasswordTitle'),
            'content'   => $this->makePartial('forgot-password'),
        ]);
    }    

    /**
     * Render the registration form.  Override the partial "register-form" 
     * in the active theme to customize the registration form
     */
    public function onRegister()
    {
        $options = Usermeta::getOptions();

        return $this->renderPartial('@modalDisplay', [
            'title'     => Lang::get('dma.friends::lang.userLogin.registerTitle'),
            'content'   => $this->makePartial('register-form', [ 'options' => $options ]),
        ]);
    }
    
    /**
     * Verify if the given user mail is not already register
     */
    public function onAvailableUser()
    {
        $email = post('email');
        $data = ['available' =>  true];
        if ( User::where('email', $email)->count() > 0 ) {
            $data['available'] = false;
        }
        return $data;
    }

    /**
     * Submit handler for registration
     */
    public function onRegisterSubmit()
    {
        $data = post();

        $rules = [
            'first_name'            => 'required|min:2',
            'last_name'             => 'required|min:2',
            //'username'              => 'required|min:6',
            'email'                 => 'required|email|between:2,64',
            'password'              => 'required|min:6',
            'password_confirmation' => 'required|min:6',
        ];

        $validation = Validator::make($data, $rules);
        if ($validation->fails())
            throw new ValidationException($validation);

        /*
         * Register user
         */
        $requireActivation = UserSettings::get('require_activation', true);
        $automaticActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_AUTO;
        $userActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_USER;

        // Split the data into whats required for the user and usermeta models
        $userData = [
            'username'              => substr(md5($data['email']), 0, 9), //$data['username'],
            'password'              => $data['password'],
            'password_confirmation' => $data['password_confirmation'],
            'email'                 => $data['email'],
            'street_addr'           => $data['street_addr'],
            'city'                  => $data['city'],
            'state'                 => $data['state'],
            'zip'                   => $data['zip'],
            'phone'                 => $data['phone'],
        ];

        $user = Auth::register($userData, $automaticActivation);

        $birth_date = $data['birthday']['year'] 
            . '-' .  sprintf("%02s", $data['birthday']['month']) 
            . '-' .  sprintf("%02s", $data['birthday']['day'])
            . ' 00:00:00';

        // Save user metadata
        $usermeta = new Usermeta;
        $usermeta->first_name       = $data['first_name'];
        $usermeta->last_name        = $data['last_name'];
        $usermeta->gender           = $data['gender'];
        $usermeta->birth_date       = $birth_date;
        $usermeta->race             = $data['race'];
        $usermeta->household_income = $data['household_income'];
        $usermeta->household_size   = $data['household_size'];
        $usermeta->education        = $data['education'];
        $usermeta->email_optin      = isset($data['email_optin']) ? $data['email_optin'] : false;

        $user->metadata()->save($usermeta);

        UserExtend::uploadAvatar($user, $data['avatar']);

        /*
         * Activation is by the user, send the email
         */
        if ($userActivation) {
            $this->sendActivationEmail($user);
        }

        /*
         * Automatically activated or not required, log the user in
         */
        if ($automaticActivation || !$requireActivation) {
            Auth::login($user);
        }

        /*
         * Fire event that user has registered
         */
        Event::fire('auth.register', [$user]);

        /*
         * Redirect to the intended page after successful sign in
         */
        $redirectUrl = $this->pageUrl($this->property('redirect'));

        if ($redirectUrl = post('redirect', $redirectUrl))
            return Redirect::intended($redirectUrl);
    }

    public function getAvatars()
    {
        $avatars = [];

        $themePath = UserLogin::getThemeDir();
        $avatarPath = $themePath . '/assets/images/avatars/*.jpg';

        // loop through all the files in the plugin's avatars directory and parse the file names
        foreach ( glob($avatarPath ) as $file ) { 
            $path = str_replace(base_path(), '', $file);

            $avatars[] = $path;
        }   

        return $this->renderPartial('@avatars', ['avatars' => $avatars]);

    }

    /**
     * Implementation of ViewMaker->makePartial that renders a partial in
     * a modal dialog and can accept a partial from the active theme
     *
     * @param string $partial
     * Name of the partial to be rendered.  The partial must reside in the active
     * theme's "partials" directory
     *
     * @param array $params
     * An array of parameters to be passed to makePartial.  See Backend\Traits\ViewMaker 
     * for details
     *
     * @param boolean $throwException
     * if true an exception will be thrown if the partial is not available
     *
     * @return string $content
     * A rendered partial
     */
   
    public function makePartial($partial, $params = [], $throwException = true)
    {   
        $partialsDir = self::getThemePartialsDir();
        $partialPath = $partialsDir . $partial . '.htm';

        if (!File::isFile($partialPath)) {
            $partialPath = $this->getViewPath($partial) . '.htm';
        }

        if (!File::isFile($partialPath)) {
            if ($throwException)
                throw new SystemException(Lang::get('backend::lang.partial.not_found', ['name' => $partialPath]));
            else
                return false;
        }   

        return $this->makeFileContents($partialPath, $params);
    }   

    /**
     * Get the path to the theme's partials
     *
     * @return string $path
     */ 
    protected static function getThemePartialsDir()
    {
        return self::getThemeDir() . '/partials/';
    }

    /**
     * Get the path to the theme's partials
     *
     * @return string $path
     */ 
    protected static function getThemeDir()
    {
        $theme = Theme::getActiveTheme();
        return $theme->getPath();
    }

    public function loadAssets()
    {
        $base_path = '../../../modules/backend/formwidgets/datepicker/assets/';

        $this->addCss($base_path . 'vendor/pikaday/css/pikaday.css', 'core');
        $this->addCss($base_path . 'vendor/clockpicker/css/jquery-clockpicker.css', 'core');
        $this->addCss($base_path . 'css/datepicker.css', 'core');
        $this->addJs($base_path . 'vendor/moment/moment.js', 'core');
        $this->addJs($base_path . 'vendor/pikaday/js/pikaday.js', 'core');
        $this->addJs($base_path . 'vendor/pikaday/js/pikaday.jquery.js', 'core');
        $this->addJs($base_path . 'vendor/clockpicker/js/jquery-clockpicker.js', 'core');
        $this->addJs($base_path . 'js/datepicker.js', 'core');
        $this->addJs($base_path . 'js/timepicker.js', 'core');
    }
}