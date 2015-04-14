<?php namespace DMA\Friends\Facades;

use Illuminate\Support\Facades\Facade;

class FriendsAPI extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * Resolves to:
     * - DMA\Friends\Classes\API\APIManager
     *
     * @return string
     */
    protected static function getFacadeAccessor(){ 
        return 'FriendsAPI';
    }
}
