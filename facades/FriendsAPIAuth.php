<?php namespace DMA\Friends\Facades;

use Illuminate\Support\Facades\Facade;


class FriendsAPIAuth extends Facade
{

    // TODO: API Authentication should be merge or be accesible from the AuthManager, but 
    // First AuthManager must be comverted into a Facade.
    
    /**
     * Get the registered name of the component.
     *
     * Resolves to:
     * - DMA\Friends\Classes\API\Auth\APIAuthManager
     *
     * @return string
     */
    protected static function getFacadeAccessor(){ 
        return 'FriendsAPIAuth';
    }
}
