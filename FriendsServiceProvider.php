<?php

namespace DMA\Friends;

use Illuminate\Support\ServiceProvider;
use DMA\Friends\Classes\ActivityCode;
use DMA\Friends\Classes\BadgeProcessor;
use DMA\Friends\Classes\FriendsLog;
use DMA\Friends\Classes\Notifications\ChannelManager;

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


    /**
     * Helper method to quickly setup class aliases for a service
     *
     * @return void
     */
    protected function createAlias($alias, $class)
    {
        // History :
        // 03-09-2015 : It seems that L5 don't raise booting events any more
        // because this Aliases are not been created. Skipping the event
        // add registering the alias directly works. Keeping code for reference
        // if an expected bug appers because the alias are created before the app
        // starts.
        /*
        $this->app->booting(function() use ($alias, $class)
        {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias($alias, $class);
        });
        */
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias($alias, $class);

    }
}
