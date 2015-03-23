<?php namespace DMA\Friends\API\Resources;

use Request;
use Response;
use Exception;
use Validator;
use Auth;

use DMA\Friends\Models\Usermeta;
use DMA\Friends\Models\UserExtend;
use DMA\Friends\Classes\API\BaseResource;
use RainLab\User\Models\Settings as UserSettings;

use October\Rain\Database\ModelException;

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
    
    public function getTransformer()
    {
        $profile = $this->include_profile;
        $this->include_profile = false;
        return new $this->transformer($profile);
        
    }
    
    public function show($id)
    {
        // Hacky variable to make the user transformter 
        // to include the user profile
        $this->include_profile = true;
        return parent::show($id);
    }
    
    public function store()
    {
        // TODO : This logic may need to be in the Extend User model
        try{
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
            if ($validation->fails()){
                return $this->errorDataValidation('User data fails to validated', $validation->errors());
                throw new Exception();
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
            
            $avatar = array_get($data,'avatar', null);
            if (!is_null($avatar)) {
                UserExtend::uploadAvatar($user, $avatar);
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
    
    
    /**
     * Generates a response with a 422 HTTP header a given message and given errors.
     *
     * @param string $message
     * @param array $errors
     * @return mixed
     */
    private function errorDataValidation($message = 'Invalid data ', $errors = [])
    {
        return Response::api()->setStatusCode(422)->withArray([
            'error' => [
                'code' => 422,
                'http_code' => 'GEN-UNPROCESSABLE',
                'message' => $message,
                'errors' => $errors
            ]
        ]);
    }
}