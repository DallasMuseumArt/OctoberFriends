<?php

namespace DMA\Friends;

use Illuminate\Support\ServiceProvider;
use DMA\Friends\Classes\ActivityCode;
use DMA\Friends\Classes\BadgeProcessor;
use DMA\Friends\Classes\FriendsLog;

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

    /**
     * Helper method to quickly setup class aliases for a service
     * 
     * @return void
     */
    protected function createAlias($alias, $class)
    {
        $this->app->booting(function() use ($alias, $class)
        {   
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias($alias, $class);
        }); 
    }
}
