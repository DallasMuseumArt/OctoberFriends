<?php namespace DMA\Friends\API\Resources;

use Request;
use FriendsAPIAuth;
use RainLab\User\Models\User;

trait UserTransformerInjectionTrait {
    
    /**
     * Exclude filters from the list of parameters
     * in the url
     * 
     * @return array
     */
    protected function getExcludedFiltersList()
    {
        $parameters = parent::getExcludedFiltersList();
        $parameters = is_array($parameters) ? $parameters : [];
        $parameters[] = 'user';
        return $parameters;
    }
    
    
    /**
     * If the transformer supports user injection. Inject the request user 
     * found in the request data.
     * 
     * @return DMA\Friends\Classes\API\BaseTransformer
     */
    protected function getTransformer()
    {
        $transformer = parent::getTransformer();   
        $data = Request::all();

        if($user_id = array_get($data, 'user', null)){
            if($user = User::find($user_id)){
    
                if(method_exists($transformer, 'setUser')){
                    $transformer->setUser($user);
                    
                    // Validated that this authentication token allow
                    // to display data of other users
                    // And exception is throw is user don't have the proper
                    // permissions
                    FriendsAPIAuth::validatedUserAccess($user);
                }
    
            }
        }
        return $transformer;
    }
}