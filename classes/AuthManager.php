<?php namespace DMA\Friends\Classes;

use Log;
use FriendsAuth;

/**
 * DEPRECATED
 * 
 * Manage custom authentication for friends
 *
 * @package DMA\Friends\Classes
 * @author Kristen Arnold, Carlos Arroyo
 */
class AuthManager
{
    /** 
     * WARNING
     * 
     * This class will redirect all static calls to FriendsAuth facade.
     * AuthManager is keep for backawards compatibility just is case is 
     * been use by other Friends plugins.  
     * 
     * This class was deprecated because was full of static methods causing 
     * unnecessary difficulty to extend the funtionality of this class.
     * 
     */ 
    
    
    public static function __callStatic($name, $arguments)
    {
        Log::warning("AuthManager is now in deprecation. please use FriendsAuth Facade instead of.");
        return call_user_func_array("FriendsAuth::$name", $arguments);
    }
    
}
