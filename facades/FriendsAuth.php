<?php namespace DMA\Friends\Facades;

use Illuminate\Support\Facades\Facade;


class FriendsAuth extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * Resolves to:
     * - DMA\Friends\Classes\AuthManager
     *
     * @return string
     */
    protected static function getFacadeAccessor(){ 
        return 'FriendsAuth';
    }
}
