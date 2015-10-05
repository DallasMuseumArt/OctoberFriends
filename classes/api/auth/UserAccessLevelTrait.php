<?php namespace DMA\Friends\Classes\API\Auth;

use Response;
use Exception;
use FriendsAPIAuth;
use Log;

trait UserAccessLevelTrait
{



    /**
     * Get action methods that require to check
     * if the user can perform the action
     * 
     * @Return array
     * @throws Exception
     */
    protected function getUserRestrictiveActions()
    {
        if(!@$this->checkAccessLevelActions){
            Log::error( get_class($this) . 'is Using UserAccessLevelTrait but "checkAccessLevelActions" array is not defined' );
            throw new Exception('Can verify access levels. Check logs.');
        }
        return $this->checkAccessLevelActions;
    }

    
    
    
    /**
     * (non-PHPdoc)
     * @see \Illuminate\Routing\Controller::callAction()
     */
    public function callAction($method, $parameters)
    {
        $check = $this->getUserRestrictiveActions();
        
        
        if (in_array($method, $check)){
            try{ 
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
                FriendsAPIAuth::validateUserAccess($user);
            }catch(Exception $e){
                return Response::api()->errorForbidden($e->getMessage());
            }
        }

        // If the check pass execute the action 
        return parent::callAction($method, $parameters);
    
    }


}
