<?php namespace DMA\Friends\Components;

use Cms\Classes\ComponentBase;
use Redirect;
use Validator;
use October\Rain\Support\ValidationException;
use RainLab\User\Models\Settings as UserSettings;
use Cms\Classes\Theme;
use System\Classes\SystemException;
use Cms\Classes\Page;
use File;
use Lang;
use Auth;
use Flash;
use DMA\Friends\Wordpress\Auth as WordpressAuth;

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

    public function onLogin()
    {        
        return $this->renderPartial('@modalDisplay', [
            'title'     => Lang::get('dma.friends::lang.userLogin.loginTitle'),
            'content'   => $this->makePartial('login-form'),
        ]);
    }

    public function onUserLogin()
    {
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
         * Validate user credintials
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
         * Redirect to the intended page after successful sign in
         */
        $redirectUrl = $this->pageUrl($this->property('redirect'));

        if ($redirectUrl = post('redirect', $redirectUrl))
            return Redirect::intended($redirectUrl);
    }

    public function onRegister()
    {

        return $this->renderPartial('@modalDisplay', [
            'title'     => Lang::get('dma.friends::lang.userLogin.registerTitle'),
            'content'   => $this->makePartial('register-form'),
        ]);
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
        $theme = Theme::getActiveTheme();
        return $theme->getPath() . '/partials/';
    }
}