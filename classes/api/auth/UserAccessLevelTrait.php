<?php namespace DMA\Friends\Classes\API\Auth;

use Response;
use Request;
use Exception;
use FriendsAPIAuth;
use Log;

trait UserAccessLevelTrait
{

    /**
     * Get action methods that don't require to check
     * if the user can perform the action
     * 
     * @Return array
     */
    protected function getUnRestrictivedActions()
    {
        if(!@$this->skipUserPermissionValidation){
            $this->skipUserPermissionValidation = [];
        }
        return $this->skipUserPermissionValidation;        
    }

    /**
     * Validate if request user match with the authentication 
     * token. 
     * 
     * @param string $method
     * @param string $parameters
     * @throws \DMA\Friends\Classes\API\Auth\Exceptions\UserAccessDenied
     */
    public function validatedUserAccess($method, $parameters){
        // Skip user validation if action is under the unrestricted list 
        // or is a public action that don't required user level validation
        $skip = array_merge($this->getUnRestrictivedActions(), $this->publicActions);
        if (!in_array($method, $skip)){

            // Search for variables in URL and/or Body that match any of the possible
            // variables that contains the user id.
            
            // Please note that variables comming by the URL parameter override variables
            // with the same name in the Body request. eg. A router with the following URL
            // foo/user/{user}/bar via POST with a body request { user: 2, var: 234 } the
            // user variable { user } will be the value set by the router.
            
            // TODO: Find a way to declare the name of the user variable. A possible
            // solution is to declare it when declaring the route in the method
            // addAdditionalRoute of the BaseResource Class
            $userVars = ['user', 'user_id', 'id'];
            
            $data = array_merge(Request::all(), $parameters);
            $user = $this->extractUserId($data, $userVars);
           
            // Validate if user has access to this resource
            // If the validateUserAccess throws and execption when
            // user don't have access
            FriendsAPIAuth::validatedUserAccess($user);
        }
        
        
    }
    
    /**
     * Try to find user id in the given data. This method 
     * will return the first variable found.
     * 
     * @param array $data Parameters and Request data combined
     * @param array $userVars 
     * Array of names of possible variables that
     * contain the user id in the given data. Priority is base on 
     * the order of this list.
     */
    protected function extractUserId($data, $userVars)
    {        
        // Check if any of the given user variable names
        // is declared either in the parameters or body of the request
        foreach($userVars as $name){
           
            $user = array_get($data, $name, null);
            if ($user){
                return $user;
            }
        }
        // not user found lets try first paramter
        return array_get($data, @array_keys($data)[0], null); 
    }
 

}
