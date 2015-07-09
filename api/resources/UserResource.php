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
        $this->addAdditionalRoute('userActivities', '{user}/activities',        ['GET']);
        $this->addAdditionalRoute('userRewards',    '{user}/rewards',           ['GET']);
        $this->addAdditionalRoute('userBadges',     '{user}/badges',            ['GET']);
        
    }
    
    
    
    public function getTransformer()
    {
        $profile = $this->include_profile;
        $this->include_profile = false;
        return new $this->transformer($profile);
        
    }

    /**
     * 
     * @SWG\Definition(
     *      definition="request.user.credentials",
     *      required={"username", "password"},
     *      @SWG\Property(
     *         property="username",
     *         type="string"
     *      ),
     *      @SWG\Property(
     *         property="password",
     *         type="string"
     *      )     
     * )
     * 
     * @SWG\Post(
     *     path="users/login",
     *     description="User login",
     *     tags={ "user"},
     *     
     *     @SWG\Parameter(
     *         description="User credentials payload",
     *         name="body",
     *         in="body",
     *         required=true,
     *         schema=@SWG\Schema(ref="#/definitions/request.user.credentials")
     *     ), 
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/user.extended")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="User not found",
     *         @SWG\Schema(ref="#/definitions/UserError404")
     *     )     
     * )
     */
    
    
    
    public function login()
    {

        $data = Request::all();
        
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
            return $this->errorDataValidation('User credentials fail to validate', $validation->errors());
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
        
    }
    
    /**
     * @SWG\Get(
     *     path="users/{id}",
     *     description="Returns an user by id",
     *     tags={ "user"},
     *
     *     @SWG\Parameter(
     *         description="ID of user to fetch",
     *         format="int64",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/user")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Not Found",
     *         @SWG\Schema(ref="#/definitions/error404")
     *     )
     * )
     */
    public function show($id)
    {
        // Hacky variable to make the user transformer 
        // to include the user profile
        $this->include_profile = true;
        return parent::show($id);
    }
    
    
    /** 
     * @SWG\Get(
     *     path="users",
     *     description="Returns all users",
     *     tags={ "user"},
     *     
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/user", type="array")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Not Found",
     *         @SWG\Schema(ref="#/definitions/error404")
     *    )
     * )
     */
    public function index()
    {
        return parent::index();
    }
    
    /**
     * 
     * @SWG\Definition(
     *     definition="request.user",
     *     type="object",
     *     required={"first_name", "last_name", "email", "address", "city", "state", "zip", "phone"},
     *     @SWG\Property(
     *         property="first_name",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         property="last_name",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         property="email",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         property="email_optin",
     *         type="boolean"
     *     ),
     *     @SWG\Property(
     *         property="password",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         property="password_confirmation",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         property="address",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         description="State id. Get state using countries/{id}/states",
     *         property="state",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @SWG\Property(
     *         property="zip",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         property="phone",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         property="birthday_year",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         property="birthday_month",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         property="birthday_day",
     *         type="string"
     *     ),
     *    @SWG\Property(
     *         description="Get an update list from endpoint users/profile-options/gender",  
     *         property="gender",
     *         type="string",
     *         enum={"Male", "Female", "Non Binary/Other"}
     *    ),    
     *    @SWG\Property(
     *         description="Get an update list from endpoint users/profile-options/race",
     *         property="race",
     *         type="string",
     *         enum={"White", "Hispanic", "Black or African American", "American Indian or Alaska Native", "Asian", "Native Hawaiian or Other Pacific Islander", "Two or more races", "Other"}
     *    ),
     *    @SWG\Property(
     *         description="Get an update list from endpoint users/profile-options/household_income",  
     *         property="household_income",
     *         type="string",
     *         enum={"Less then $25,000", "$25,000 - $50,000", "$50,000 - $75,000", "$75,000 - $150,000", "$150,000 - $500,000", "$500,000 or more"}
     *    ),
     *    @SWG\Property(
     *         description="Get an update list from endpoint users/profile-options/education", 
     *         property="education",
     *         type="string",
     *         enum={"K-12", "High School/GED", "Some College", "Vocational or Trade School", "Bachelors Degree", "Masters Degree", "PhD"}
     *    )
     * )
     * 
     * @SWG\Post(
     *     path="users",
     *     description="Create a new user",
     *     tags={ "user"},
     *     
     *     @SWG\Parameter(
     *         description="User payload",
     *         name="body",
     *         in="body",
     *         required=true,
     *         schema=@SWG\Schema(ref="#/definitions/request.user")
     *     ), 
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/user.extended")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     )
     * )
     */
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
                // Let the API resource deal with the exception
                throw $e;
            }

        }
        
    }
    
    /**
     * @SWG\Put(
     *     path="users/{id}",
     *     description="Update a given user",
     *     tags={ "user"},
     *     @SWG\Parameter(
     *         description="ID of user to fetch",
     *         format="int64",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         description="User payload",
     *         name="body",
     *         in="body",
     *         required=true,
     *         schema=@SWG\Schema(ref="#/definitions/request.user")
     *     ), 
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/user.extended")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="User not found",
     *         @SWG\Schema(ref="#/definitions/UserError404")
     *     )
     *     
     * )
     */
    
    /**
     * (non-PHPdoc)
     * @see \DMA\Friends\Classes\API\BaseResource::update()
     */
    
    public function update($id)
    {
        try{
            if(is_null($user = User::find($id))){
                return Response::api()->errorNotFound('User not found'); 
            }
            
            $data =  Request::all();
            if(Request::isJson() && $data == ''){
                // Is JSON and data is empty, By default PHP
                // blocks PUT/PATCH methods. So as workaround 
                // get get the content and decode it manually
                // if required
                $data = Request::getContent();
                if (!is_array($data)){
                    $data = json_decode($data);
                }
            }

            $rules = [
                    'first_name'            => 'min:2',
                    'last_name'             => 'min:2',
                    //'username'              => 'required|min:6',
                    'email'                 => 'email|between:2,64',
                    'password'              => 'sometimes|required|confirmed|min:6',
                    'password_confirmation' => 'min:6'
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
            
            \Log::debug($data);

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
               
            }else{
                throw new Exception('Failed to update user data');
            }
            
          
            return $this->show($user->id);
                               
        } catch(Exception $e) {
            if ($e instanceof ModelException) {
                return $this->errorDataValidation($e->getMessage());
            } else {
                // Let the API resource deal with the exception
                throw $e;
            }

        }
    }
    
    
    /**
     * @SWG\Definition(
     *     definition="request.avatar",
     *     type="object",
     *     required={"source"},
     *     @SWG\Property(
     *         description="Source can be one of the URLs returned by  users/profile-options/avatar endpoint or by uploading a Base64 encode string of a JPG, PNG or GIF",
     *         property="source",
     *         type="string"
     *     )
     * )
     * 
     * @SWG\Post(
     *     path="users/{id}/upload-avatar",
     *     description="Change user avatar. Avatar must be a valid JPG, GIF or PNG. And not bigger that 400x400 pixels.",
     *     tags={ "user"},
     *
     *     @SWG\Parameter(
     *         description="ID of user to fetch",
     *         format="int64",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *
     *     @SWG\Parameter(
     *         description="Avatar payload",
     *         name="body",
     *         in="body",
     *         required=true,
     *         schema=@SWG\Schema(ref="#/definitions/request.avatar")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(
     *              @SWG\Property(
     *                  property="success",
     *                  type="boolean"
     *              )
     *         )
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="User not found",
     *         @SWG\Schema(ref="#/definitions/UserError404")
     *     )
     * )
     */
    
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
  
    
    /**
     * @SWG\Definition(
     *     definition="response.profile.options",
     *     type="object",
     *     @SWG\Property(
     *          property="gender",
     *          type="array",
     *          items=@SWG\Schema(type="string")
     *     ),     
     *     @SWG\Property(
     *          property="race",
     *          type="array",
     *          items=@SWG\Schema(type="string")
     *     ),     
     *     @SWG\Property(
     *          description="Currently hardcode to return only USA states",
     *          property="states",
     *          type="array",
     *          items=@SWG\Schema(ref="#/definitions/state")
     *     ),     
     *     @SWG\Property(
     *          property="household_income",
     *          type="array",
     *          items=@SWG\Schema(type="string")
     *     ),     
     *     @SWG\Property(
     *          property="education",
     *          type="array",
     *          items=@SWG\Schema(type="string")
     *     ),     
     *     @SWG\Property(
     *          property="avatars",
     *          type="array",
     *          items=@SWG\Schema(type="string")
     *     ) 
     * )
     * 
     * 
     * @SWG\Get(
     *     path="users/profile-options/{field}",
     *     description="Returns an user by id",
     *     tags={ "user"},
     *
     *     @SWG\Parameter(
     *         description="Return options only for the given field",
     *         in="path",
     *         name="field",
     *         required=false,
     *         type="string",
     *         enum={"gender", "race", "states", "household_income", "education", "avatars"}
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/response.profile.options")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Not Found",
     *         @SWG\Schema(ref="#/definitions/error404")
     *     )
     * )
     */
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
    
    /**
     * @SWG\Get(
     *     path="users/{id}/activities",
     *     description="Returns an user by id",
     *     tags={ "user"},
     *
     *     @SWG\Parameter(
     *         description="ID of user to fetch",
     *         format="int64",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/activity")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Not Found",
     *         @SWG\Schema(ref="#/definitions/error404")
     *     )
     * )
     */
    public function userActivities($userId)
    {
        $transformer  = '\DMA\Friends\API\Transformers\ActivityTransformer';
        $attrRelation = 'activities';
        return $this->genericUserRelationResource($userId, $attrRelation, $transformer);
    }
    
    /**
     * @SWG\Get(
     *     path="users/{id}/rewards",
     *     description="Returns an user by id",
     *     tags={ "user"},
     *
     *     @SWG\Parameter(
     *         description="ID of user to fetch",
     *         format="int64",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/reward")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Not Found",
     *         @SWG\Schema(ref="#/definitions/error404")
     *     )
     * )
     */
    public function userRewards($userId)
    {
        $transformer  = '\DMA\Friends\API\Transformers\RewardTransformer';
        $attrRelation = 'rewards';
        return $this->genericUserRelationResource($userId, $attrRelation, $transformer);
    }
    
    /**
     * @SWG\Get(
     *     path="users/{id}/badges",
     *     description="Returns an user by id",
     *     tags={ "user"},
     *
     *     @SWG\Parameter(
     *         description="ID of user to fetch",
     *         format="int64",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/badge")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Not Found",
     *         @SWG\Schema(ref="#/definitions/error404")
     *     )
     * )
     */
    public function userBadges($userId)
    {
        $transformer  = '\DMA\Friends\API\Transformers\BadgeTransformer';
        $attrRelation = 'badges';
        return $this->genericUserRelationResource($userId, $attrRelation, $transformer);
    }
    
    private function genericUserRelationResource($userId, $attrRelation, $transformer)
    {
        if(is_null($user = User::find($userId))){
            return Response::api()->errorNotFound('User not found');
        }
    
        $pageSize   = $this->getPageSize();
        $transformer = new $transformer;
    
        if ($pageSize > 0){
            $paginator = $user->{$attrRelation}()->paginate($pageSize);
            return Response::api()->withPaginator($paginator, $transformer);
        }else{
            return Response::api()->withCollection($user->{$attrRelation}, $transformer);
        }
    
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
