<?php namespace DMA\Friends\Classes;

use Auth;
use Model;
use Event;
use Validator;
use Exception;
use ValidationException;
use RainLab\User\Models\Settings as UserSettings;
use RainLab\User\Models\User;
use DMA\Friends\Models\Usermeta;
use System\Classes\PluginManager;
use DMA\Friends\Classes\FriendsMembershipInterface;

/**
 * Manage custom authentication for friends
 *
 * @package DMA\Friends\Classes
 * @author Kristen Arnold, Carlos Arroyo
 */
class AuthManager
{
    
    protected static $membershipDrivers;

    /**
     * Authenticate a user by either username, email, or member id
     *
     * @param array $data
     * An array of attributes to authenticate a user minimially requires the following
     * - login
     *      Provide either the email address or username to authenticate.  This 
     *      setting is configured in the administrative backend
     * - password
     *      A password
     * - no_password
     *      If true password authentication will be bypassed.  Use with caution
     *      as this can be a security breach if used incorrectly.
     *
     * @param array $rules
     * A set of validation rules to validate against
     * see http://laravel.com/docs/5.1/validation
     *
     * @return User $user
     * returns the authenticated user object
     */
    public static function auth($data, $rules = [])
    {
        $user = false;

        // Provide default rules
        $rules += [
            'login'     => 'required|between:4,64',
            'password'  => 'required|min:4',
        ];

        // Fire prelogin event before we start processing the user
        Event::fire('auth.prelogin', [$data, $rules]);

        if (!isset($data['no_password'])) {
            $data['no_password'] = false;
        }

        if (!$data['no_password']) {

            /*
             * Validate user credentials
             */
            $validation = Validator::make($data, $rules);
            if ($validation->fails()) {

                throw new ValidationException($validation);
            }

        }

        // Attempt to lookup by member_id
        if (!$user) {
            $user = self::isMember($data['login']);
        }

        // Attempt to look up barcode
        if (!$user) {
            $user = self::isBarcode($data['login']);
        }
   
        $membership = null;
        $skipInvalidLogin = false;
        
        // Authentication cases:
        // This cases are a re-factory of the previous AuthManager implementation
        if ($user && $data['no_password'] ) {
            // 1. User exists and the login done via a card (OK)
            Auth::login($user);
            
        } else if (!$user && $data['no_password'] ) {
            // 2. Not user found and the login done via a card (OK)
            // Throw invalid login if unsuccesful
            $membership = self::lookupMembershipInfo($data);
            
        } else if ($user && !$data['no_password'] ) {
            // 3. Login is not done via a card and user exists in friends  (OK)
            // Throw invalid login if unsuccesful 
            $user = self::loginUser($user, $data);
            
        }  else if (!$user && !$data['no_password'] ) {
            // 4. User don't exists in friends (OK)
            // Throw invalid login if unsuccesful
            $membership = self::lookupMembershipInfo($data);
            if (!$membership) {
                try {
                     $user = self::loginUser($user, $data);
                }  catch(Exception $e) {
                    $skipInvalidLogin = true;
                    // Keep for backwards compatibilty with previous implementation of 
                    // AuthManager where membership lookup dependent on auth.invalidLogin event
                    $throw = Event::fire('auth.invalidLogin', [$data, $rules]);
                    // Re-throw same exception if user is not defined
                    if (!$throw) throw $e;
                }
            }
        }
        

        if ($membership) {
            Event::fire('auth.verify.request', $membership);
            return $membership;
        }
        
        if ($user) {
            Event::fire('auth.login', $user);
            return $user;
        }
        
        if (!$user && !$membership && !$skipInvalidLogin) {
            Event::fire('auth.invalidLogin', [$data, $rules]);
            return false;
        }

        return false;
    }

    /**
     * Lookup user by member id
     *
     * @param string $id
     * An id to lookup by member id
     *
     * @return User $user
     * A RainLab\User\Model\User object
     */
    private static function isMember($id)
    {
        $usermeta = Usermeta::with('user')->where('current_member_number', '=', $id)->first();
        if (isset($usermeta->user) && !empty($usermeta->user)) {
            $user = $usermeta->user;

            return $user;
        } 

        return false;
    }

    /**
     * Lookup user by barcode id
     *
     * @param string $id
     * An id to lookup by barcode id
     *
     * @return User $user
     * A RainLab\User\Model\User object
     */
    private static function isBarcode($id)
    {
        $user = User::where('barcode_id', $id)->first();

        if ($user) {
            return $user;
        }

        return false;
    }

    /**
     * Attempt to authenticate a user with a password
     *
     * @param User $user
     * A RainLab\User\Model\User object
     * 
     * @param array $data
     * An array of paramaters for authenticating.
     *
     * @return User $user
     * A RainLab\User\Model\User object of the authenticated user
     */
    private static function loginUser($user, $data)
    {
        $loginAttribute = UserSettings::get('login_attribute', UserSettings::LOGIN_EMAIL);

        if ($user) {
            if ($loginAttribute == UserSettings::LOGIN_USERNAME) {
                $data['login'] = $user->username;
            } else {
                $data['login'] = $user->email;
            }

        }

        /*  
         * Authenticate user
         */
        $user = Auth::authenticate([
            'login'     => array_get($data, 'login'),
            'password'  => array_get($data, 'password')
        ], true);
 
        return $user;
    }


    /**
     * Register a user
     *
     * @param array $data
     * An array of attributes to register a user.
     * Any fields that are not properties on the user object
     * Will be applied to the Usermeta object
     *
     * @param array $rules
     * A set of validation rules to validate against
     * see http://laravel.com/docs/5.1/validation
     *
     * @return User $user
     * return the user object after registration
     */
    public static function register($data, $rules = [])
    {

        $rules += [
            'first_name'            => 'required|min:2',
            'last_name'             => 'required|min:2',
            'email'                 => 'required|email|between:2,64',
            'password'              => 'required|min:6',
            'password_confirmation' => 'required|min:6',
        ];

        Event::fire('auth.preRegister', [$data, $rules]);

        $validation = Validator::make($data, $rules);
        if ($validation->fails())
            throw new ValidationException($validation);

        /*
         * Register user
         */
        $requireActivation = UserSettings::get('require_activation', true);
        $automaticActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_AUTO;
        $userActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_USER;

        /*
         * Data altercations
         */
        $data['first_name']     = ucwords($data['first_name']);
        $data['last_name']      = ucwords($data['last_name']);
        $data['birth_date']     = isset($data['birthday']) ? UserExtend::parseBirthdate($data['birthday']) : "";
        $data['phone']          = isset($data['phone']) ? UserExtend::parsePhone($data['phone']) : "";
        $data['email_optin']    = isset($data['email_optin']) ? $data['email_optin'] : false;

        // Split the data into whats required for the user and usermeta models
        $userData = [
            'name'                  => $data['first_name'] . ' ' . $data['last_name'],
            'password'              => $data['password'],
            'password_confirmation' => $data['password_confirmation'],
            'email'                 => $data['email'],
            'street_addr'           => isset($data['street_addr']) ? $data['street_addr'] : "",
            'city'                  => isset($data['city']) ? $data['city'] : "",
            'state'                 => isset($data['state']) ? $data['state'] : "",
            'zip'                   => isset($data['zip']) ? $data['zip'] : "",
            'phone'                 => isset($data['phone']) ? $data['phone'] : "",
        ];

        $user = Auth::register($userData, $automaticActivation);

        // Save user metadata
        $usermeta = Usermeta::create($data);

        $user->metadata()->save($usermeta);

        if (isset($data['avatar'])) {
            UserExtend::uploadAvatar($user, $data['avatar']);
        }
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

        if ($user) {
            /*
             * Fire event that user has registered
             */
            Event::fire('auth.register', [$user]);

            return $user;
        }

        return false;
    }
    
    private static function lookupMembershipInfo($data)
    {
        // Before raise an exception try to find user via
        // registered plugins that have implemented a friends
        // membership interface
        $membership = null;
        foreach (self::getMembershipDrivers() as $pluginId => $interface){
            $membership =  $interface->retriveByCredentials($data);
            if ($membership) {
                // Get plugin hints
                $hintAttrs = $interface->getMembershipHintsAttributes();
                $hintAttrs = (is_array($hintAttrs))?$hintAttrs:[];
                 
                $hints = [];
                $re = "/(?P<initials>^.{2}|\\s.{2})|@(?P<domain>.{2})/i";
                foreach ($hintAttrs as $attr ){
                    $value = array_get($membership, $attr, $membership->{$attr});
                    $value = trim(strtolower($value));
                     
                    // Extract intial characters
                    preg_match_all($re, $value, $matches);
        
                    $hint = '';
                    foreach(@$matches['initials'] as $initial){
                        $initial = ucfirst(strtolower(trim($initial)));
                        if($initial){
                            $hint = $hint . $initial . str_repeat ( '*' , 8 ) . ' ';
                        }
                    }
                     
                    $hint = trim($hint);
                     
                    $domain   = @$matches['domain'][1];
                     
                    if( $domain ) {
                        $hint = $hint . '@' . $domain . str_repeat ( '*' , 5 ) . '.**' ;
                    }
                     
                    $hints[$attr] = $hint;
        
                }
                
                // Inject membership model classname 
                // if membership is a Eloquent Model
                if( $membership instanceof Model ){
                    $membership->classname = get_class($membership);
                }
                
                // Found membership information 
                return [
                        "membership" => $membership,
                        "pluginId"   => $pluginId,
                        "hints"      => $hints
                ];
                
                // Matching membership found
                break;
            }
        }
        return $membership;
    }
    
    /**
     * Loads registered FriendAPI resources from modules and plugins
     * @return void
     */
    private static function getMembershipDrivers()
    {
        
        $membershipDrivers = static::$membershipDrivers;
        if ($membershipDrivers === null) {
            $membershipDrivers = [];
            
            $plugins = PluginManager::instance()->getPlugins();
            foreach ($plugins as $pluginId => $pluginObj) {
                $interface = null;
                if(method_exists($pluginObj, 'registerFriendsMembershipInterface')) {
                    $interface = $pluginObj->registerFriendsMembershipInterface();
                    if ($interface !== null) {
                        $obj =  new $interface;
                        if ( $obj instanceof FriendsMembershipInterface ) {
                            $membershipDrivers[$pluginId] = new $interface;
                        }
                    }
                }
            }
            
            static::$membershipDrivers = $membershipDrivers;
        }
        
        return $membershipDrivers;
    }
    
    
    public static function verifyMembership($pluginId, $membershipData, $inputData)
    {        
        if ($instance = array_get(static::getMembershipDrivers(), $pluginId, null)) {
            $membership = static::loadMembership($membershipData);
            return $instance->verifyMembership($membership, $inputData);
        }
        return false;
        
    }
    
    public static function saveMembership($pluginId, $user, $membershipData)
    {
        if ($instance = array_get(static::getMembershipDrivers(), $pluginId, null)) {
            $membership = static::loadMembership($membershipData);
            return $instance->saveMembership($user, $membership);
        }
        return false;
    
    }
    
    private static function loadMembership($membershipData)
    {
        $instance = null;
        if($classname = $membershipData['classname']) {
            $model = new $classname;
            $keyName = $model->getKeyName();
            if( $id = array_get($membershipData, $keyName, null)) {
                $instance = $classname::where($keyName, $id)->first();
            }
            
        }
        
        return ($instance)?$instance : $membershipData;
    }
    
}
