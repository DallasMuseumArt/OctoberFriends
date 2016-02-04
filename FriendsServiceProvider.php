<?php

namespace DMA\Friends;

use Illuminate\Support\ServiceProvider;
use DMA\Friends\Classes\ActivityCode;
use DMA\Friends\Classes\BadgeProcessor;
use DMA\Friends\Classes\FriendsLog;
use DMA\Friends\Classes\Notifications\ChannelManager;
use DMA\Friends\Classes\API\APIManager;
use DMA\Friends\Classes\API\Auth\APIAuthManager;
use DMA\Friends\Classes\Mailchimp\MailchimpManager;
// use DMA\Friends\Classes\FriendsAuthManager;

/**
 * Register service providers for Friends
 *
 * @package DMA\Friends
 * @author Kristen Arnold, Carlos Arroyo
 */
class FriendsServiceProvider extends ServiceProvider
{
    /**
     * Register the available services in this plugin
     *
     * @return void
     */
    public function register()
    {
        $this->registerFriendsLog();
        $this->registerNotifications();
        $this->registerAPI();
        $this->registerMailChimpIntegration();
        //$this->registerFriendsAuthentication();
    }

    /**
     * Setup the ActivityCode service
     *
     * @return void
     */
    public function registerFriendsLog()
    {
        $this->app['FriendsLog'] = $this->app->share(function($app) {
            return new FriendsLog;
        });

        $this->createAlias('FriendsLog', 'DMA\Friends\Classes\FriendsLog');
    }

    
    public function registerNotifications()
    {
        // Register notification system
        $this->app['postman'] = $this->app->share(function($app)
        {
        	$channelManager = new ChannelManager;
        	$channelManager->registerChannels([
        		'\DMA\Friends\Classes\Notifications\Channels\ChannelKiosk',
        	    '\DMA\Friends\Classes\Notifications\Channels\ChannelFlash',
        	    '\DMA\Friends\Classes\Notifications\Channels\ChannelEmail',
        		'\DMA\Friends\Classes\Notifications\Channels\ChannelSMS',
        	    //'\DMA\Friends\Classes\Notifications\Channels\ChannelDummy',    
        		//'\DMA\Friends\Classes\Notifications\Channels\ChannelTwitter',
            ]);
        
        	// Register input validators
        	$channelManager->registerInputValidators([
        			'\DMA\Friends\Classes\Notifications\Inputs\InputRegex',
        			'\DMA\Friends\Classes\Notifications\Inputs\InputContains',
        			'\DMA\Friends\Classes\Notifications\Inputs\InputEquals',
        			'\DMA\Friends\Classes\Notifications\Inputs\InputStartsWith',
        			'\DMA\Friends\Classes\Notifications\Inputs\InputEndsWith'
        	]);
        	 
        	return $channelManager;
        });
                
        // Create alias Facade to the Notification manager
        $this->createAlias('Postman', 'DMA\Friends\Facades\Postman');        
    }
    
    public function registerAPI()
    {
        // API
        $this->app['FriendsAPI'] = $this->app->share(function($app) {
            \App::register('\EllipseSynergie\ApiResponse\Laravel\ResponseServiceProvider');     
            $api = new APIManager;
            return $api;            
        });
        
        $this->createAlias('FriendsAPI', 'DMA\Friends\Facades\FriendsAPI');
        
        // API Authentication
        $this->app['FriendsAPIAuth'] = $this->app->share(function($app){
            $auth = new APIAuthManager;            
            return $auth;
        });
        
        $this->createAlias('FriendsAPIAuth', 'DMA\Friends\Facades\FriendsAPIAuth');
        // Register middleware to validated API Auth tokens
        $this->registerMiddleware('friends-api-auth',
                '\DMA\Friends\Classes\API\Auth\Middleware\FriendsApiAuthMiddleware');
        
    }

    
    public function registerMailChimpIntegration()
    {
        $this->app['mailchimpintegration'] = $this->app->share(function($app) {
            $mailchimp = new MailchimpManager;
            return $mailchimp;
        });
    
        $this->createAlias('MailChimpIntegration', 'DMA\Friends\Facades\MailchimpIntegration');
        

    }

//     public function registerFriendsAuthentication()
//     {
//         $this->app['FriendsAuth'] = $this->app->share(function($app) {
//            $auth = new FriendsAuthManager;
//            return $auth;
//         });
        
//         $this->createAlias('FriendsAuth', 'DMA\Friends\Facades\FriendsAuth');
//         // Keep backwards compatibilty with previous versions of AuthManager
//         // When it was a class with static methods
//         $this->createAlias('DMA\Friends\Classes\AuthManager', 'DMA\Friends\Facades\FriendsAuth');
//     }
    
    /**
     * Helper method to quickly setup class aliases for a service
     * 
     * @return void
     */
    protected function createAlias($alias, $class)
    {
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias($alias, $class);

    }
    
    /**
     * Helper method to quickly setup middleware
     * 
     * @param string $alias
     * @param string $class
     */
    protected function registerMiddleware($alias, $class)
    {
        $this->app['router']->middleware($alias, $class);
    }
    
    
    /**
     * Get the services provided by the provider.
     * @return array
     */
    public function provides()
    {
        return ['FriendsLog', 
                'postman', 
                'FriendsAPI', 
                'FriendsAuth',
                'FriendsAPIAuth', 
                'mailchimpintegration'];
    
    }
    
    
}
