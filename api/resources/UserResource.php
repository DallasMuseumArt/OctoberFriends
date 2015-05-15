<?php namespace DMA\Friends\API\Resources;

use Auth;
use Input;
use Request;
use Response;
use Exception;
use Validator;

use DMA\Friends\Models\Usermeta;
use DMA\Friends\Classes\UserExtend;
use DMA\Friends\Classes\API\BaseResource;
use DMA\Friends\Wordpress\Auth as WordpressAuth;

use RainLab\User\Models\User;
use RainLab\User\Models\Settings as UserSettings;

use October\Rain\Database\ModelException;
use Cms\Classes\Theme;

class UserResource extends BaseResource
{
    protected $model        = '\RainLab\User\Models\User';
    
    protected $transformer  = '\DMA\Friends\API\Transformers\UserTransformer';
    
    /**
     * Hacky variable to include user profile only when 
     * showing a single user
     * @var boolean
     */
    private $include_profile = false;
    
    public function __construct()
    {
        // Add additional routes to Activity resource
        $this->addAdditionalRoute('login',          'login',                    ['POST']);
        $this->addAdditionalRoute('uploadAvatar',   '{user}/upload-avatar',     ['POST', 'PUT']);
        $this->addAdditionalRoute('profileOptions', 'profile-options/{field}',  ['GET']);
        $this->addAdditionalRoute('profileOptions', 'profile-options',          ['GET']);
    }
    
    
    
    public function getTransformer()
    {
        $profile = $this->include_profile;
        $this->include_profile = false;
        return new $this->transformer($profile);
        
    }

    public function login()
    {
        try{
            $data = Input::all();
            
            // TODO : I think this logic should be centralized 
            // Base on loginUser component
            // Update wordpress passwords if necessary
            WordpressAuth::verifyFromEmail(array_get($data, 'email'), array_get($data, 'password'));
            
            /*
             * Validate input
            */
            $rules = [
                    'password' => 'required|min:2'
            ];
            
            $loginAttribute = UserSettings::get('login_attribute', UserSettings::LOGIN_EMAIL);
            
            if ($loginAttribute == UserSettings::LOGIN_USERNAME)
                $rules['username'] = 'required|between:2,64';
            else
                $rules['username'] = 'required|email|between:2,64';
            
            if (!in_array('username', $data))
                $data['username'] = array_get($data, 'username', array_get($data, 'email'));
            
            /*
             * Validate user credentials
            */
            $validation = Validator::make($data, $rules);
            if ($validation->fails()){
                return $this->errorDataValidation('User credantials fail to validate', $validation->errors());
            }
            
            /*
             * Authenticate user
            */
            $user = Auth::authenticate([
                    'login' => array_get($data, 'username'),
                    'password' => array_get($data, 'password')
            ], true);
            
            if ($user) {
                return $this->show($user->id);
            } else {
                return Response::api()->errorNotFound('User not found');
            }
    
            
        } catch(Exception $e) {
            return Response::api()->errorInternalError($e->getMessage());
        }
    }
    

    public function show($id)
    {
        // Hacky variable to make the user transformer 
        // to include the user profile
        $this->include_profile = true;
        return parent::show($id);
    }
    
    public function store()
    {
        // TODO : This logic may need to be in the Extend User model
        try{
            $data = Request::all();
            $rules = [
                    'first_name'            => 'required|min:2',
                    'last_name'             => 'required|min:2',
                    //'username'              => 'required|min:6',
                    'email'                 => 'required|email|between:2,64',
                    'password'              => 'required|confirmed|min:6',
                    'password_confirmation' => 'required|min:6',
            ];
            
            $validation = Validator::make($data, $rules);
            if ($validation->fails()){
                return $this->errorDataValidation('User data fails to validated', $validation->errors());
            }
            /*
             * Register user
            */
            $requireActivation = UserSettings::get('require_activation', true);
            $automaticActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_AUTO;
            $userActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_USER;
            
            // Split the data into whats required for the user and usermeta models
            $userData = [
                    'name'                  => array_get($data, 'email', ''),
                    'password'              => array_get($data, 'password', null),
                    'password_confirmation' => array_get($data, 'password_confirmation', null),
                    'email'                 => array_get($data, 'email', null),
                    'street_addr'           => array_get($data, 'address', ''),
                    'city'                  => array_get($data, 'city', ''),
                    'state'                 => array_get($data, 'state', ''),
                    'zip'                   => array_get($data, 'zip', ''),
                    'phone'                 => array_get($data, 'phone', ''),
            ];
            
            // Create and register user
            $user = Auth::register($userData, $automaticActivation);
            
            $bd_year = array_get($data, 'birthday_year', null); 
            $bd_month = array_get($data, 'birthday_month', null); 
            $bd_day = array_get($data, 'birthday_day', null); 

            $birth_date = null; 
            
            if ( $bd_year && $bd_month && $bd_day ) {
                $birth_date = $bd_year
                . '-' .  sprintf("%02s", $bd_month)
                . '-' .  sprintf("%02s", $bd_day)
                . ' 00:00:00';
            }
            
            // Save user metadata
            $usermeta = new Usermeta;
            $usermeta->first_name       = array_get($data,'first_name', '');
            $usermeta->last_name        = array_get($data,'last_name', '');
            $usermeta->birth_date       = $birth_date;
            $usermeta->email_optin      = array_get($data,'email_optin', false);
            
            // Uncomment to enable demographics in registration form;
            $usermeta->gender           = array_get($data,'gender', null);
            $usermeta->race             = array_get($data,'race', null);
            $usermeta->household_income = array_get($data,'household_income', null);
            $usermeta->household_size   = array_get($data,'household_size',  null);
            $usermeta->education        = array_get($data,'education', null);
            
            $user->metadata()->save($usermeta);
            
            /*
            $avatar = array_get($data,'avatar', null);
            if (!is_null($avatar)) {
                UserExtend::uploadAvatar($user, $avatar);
            }
            */
            
            return $this->show($user->id);
                               
        } catch(Exception $e) {
            if ($e instanceof ModelException) {
                return $this->errorDataValidation($e->getMessage());
            } else {
                return Response::api()->errorInternalError($e->getMessage());
            }

        }
        
    }
    
    public function update($id)
    {
        try{
            if(is_null($user = User::find($id))){
                return Response::api()->errorNotFound('User not found'); 
            }
            
            
            $data = Request::all();
            $rules = [
                    'first_name'            => 'required|min:2',
                    'last_name'             => 'required|min:2',
                    //'username'              => 'required|min:6',
                    'email'                 => 'required|email|between:2,64',
                    'password'              => 'sometimes|required|confirmed|min:6',
                    'password_confirmation' => 'min:6',
                    'birthday'
            ];
            
            $validation = Validator::make($data, $rules);
            if ($validation->fails()){
                return $this->errorDataValidation('User data fails to validated', $validation->errors());
            }
            
            // Drop password_confirmation if password is not present
            if(is_null(array_get($data, 'password', null))) {
                unset($data['password_confirmation']);
            }
                        
            // Update user data
            $userAttrs = ['name' , 'password', 'password_confirmation', 'email',
                          'street_addr', 'city', 'state', 'zip', 'phone'];

            $user = $this->updateModelData($user, $data, $userAttrs);
            

            if($user->save()) {
                // If user save ok them we update usermetadata
                $bd_year = array_get($data, 'birthday_year', null);
                $bd_month = array_get($data, 'birthday_month', null);
                $bd_day = array_get($data, 'birthday_day', null);
                
                $birth_date = null;
                
                if ( $bd_year && $bd_month && $bd_day ) {
                    $data['birth_date'] = $bd_year
                    . '-' .  sprintf("%02s", $bd_month)
                    . '-' .  sprintf("%02s", $bd_day)
                    . ' 00:00:00';
                }
                
                // Save user metadata
                $usermeta = $user->metadata;
                if(!is_null($usermeta)){
                    $userMetadataAttrs = ['first_name' , 'last_name', 'birth_date', 'email_optin',
                                          'gender', 'race', 'household_income', 'household_size', 'education'];
                    $usermeta = $this->updateModelData($usermeta, $data, $userMetadataAttrs);
                    $usermeta->save();
                }
               
                /*
                $avatar = array_get($data,'avatar', null);
                if (!is_null($avatar)) {
                    UserExtend::uploadAvatar($user, $avatar);
                }
                */
                
                
                // TODO : Do we need to re-authenticate?
                /*
                // Re-authenticate the user
                $user = Auth::authenticate([
                        'email'     => $user->email,
                        'password'  => $data['password'],
                ], true);
                */
            }
            
          
            return $this->show($user->id);
                               
        } catch(Exception $e) {
            if ($e instanceof ModelException) {
                return $this->errorDataValidation($e->getMessage());
            } else {
                return Response::api()->errorInternalError($e->getMessage());
            }

        }
    }
    
    
    public function uploadAvatar($userId)
    {

        if(is_null($user = User::find($userId))){
            return Response::api()->errorNotFound('User not found');
        }
        
        $data = Request::all();
        $rules = [
                'source'            => 'required',
        ];
        
        $validation = Validator::make($data, $rules);
        if ($validation->fails()){
            return $this->errorDataValidation('Data fails to validated', $validation->errors());
        }        
        
        if($source = array_get($data, 'source', null)){
            
            // Check if is a selected avatar from the theme
            $avatars    = $this->getThemeAvatarOptions();
            $avatars    = array_keys($avatars);
            $keyAvatar  = trim(strtolower(basename(basename($source))));
            
            if(in_array($keyAvatar, $avatars)){
                UserExtend::uploadAvatar($user, $source);
            }else{
                UserExtend::uploadAvatarFromString($user, $source);
            }
            
            return [ 'success' => true];
            
        } 
    }
  
    
        
    public function profileOptions($field=null)
    {
        $opts = null;
        
        // Options from Usermeta
        $options = Usermeta::getOptions();
        
        // Avatar options as is used in the UserProfile component
        $options['avatars'] = $this->getThemeAvatarOptions();
        
        if (!is_null($field)) {
            $fieldOpts = array_get($options, strtolower(trim($field)), null);
            if (!is_null($fieldOpts)) {
                $opts = [ $field => $fieldOpts ];
            }else{
                $message = 'Valid fields are: [ ' . implode(', ', array_keys($options)) . ' ]' ;
                return Response::api()->errorInternalError($message);
            }
        }else{
            $opts = $options; // Return all
        }
        
        return $opts;
    }
    
    private function getThemeAvatarOptions()
    {
        $avatars = [];
        $theme = Theme::getActiveTheme();
        $themePath =  $theme->getPath();
        $avatarPath = $themePath . '/assets/images/avatars/*.jpg';
        
        // loop through all the files in the plugin's avatars directory and parse the file names
        foreach ( glob($avatarPath ) as $file ) {
            $path = str_replace(base_path(), '', $file);
        
            $avatars[trim(strtolower(basename($path)))] = $path;
        }
        
        return $avatars; 
    }
}
