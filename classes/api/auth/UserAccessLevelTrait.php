<?php namespace DMA\Friends\Classes\API\Auth;

use Response;
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
        $skip = $this->getUnRestrictivedActions() + $this->publicActions;
        
        if (!in_array($method, $skip)){
            // TODO : find a way to declare the name of the user variable in the
            // router in the declaration of checkAccessLevelActions
            // or a better solution
    
            // Asumming the 'user' parameter exits in the method
            $user = array_get($parameters, 'user', Null);
            if(!$user){
                // not user found lets try first paramter
                $user = array_get($parameters, @array_keys($parameters)[0], Null);
            }
            
            // Validate if user has access to this resource
            // If the validateUserAccess throws and execption when
            // user don't have access
            FriendsAPIAuth::validatedUserAccess($user);
        }
        
        
    }
    
    
 

}
