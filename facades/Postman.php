<?php namespace DMA\Friends\Facades;

use Illuminate\Support\Facades\Facade;

class Postman extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * Resolves to:
     * - DMA\Friends\Classes\Notifications\ChannelManager
     *
     * @return string
     */
    protected static function getFacadeAccessor(){ 
        return 'postman';
    }
}
