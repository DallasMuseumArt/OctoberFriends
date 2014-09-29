<?php

namespace DMA\Friends;

use Illuminate\Support\ServiceProvider;
use DMA\Friends\Classes\ActivityProcessor;
use DMA\Friends\Classes\BadgeProcessor;
use DMA\Friends\Classes\FriendsLog;

class FriendsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerActivityProcessor();
        $this->registerBadgeProcessor();
        $this->registerFriendsLog();
    }

    public function registerActivityProcessor()
    {
        $this->app['ActivityProcessor'] = $this->app->share(function($app) {
            return new ActivityProcessor;
        });
    }

    public function registerBadgeProcessor()
    {
        $this->app['BadgeProcessor'] = $this->app->share(function($app) {
            return new BadgeProcessor;
        });
    }

    public function registerFriendsLog()
    {
        $this->app['FriendsLog'] = $this->app->share(function($app) {
            return new FriendsLog;
        });

        $this->createAlias('FriendsLog', 'DMA\Friends\Classes\FriendsLog');
    }

    protected function createAlias($alias, $class)
    {
        $this->app->booting(function() use ($alias, $class)
        {   
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias($alias, $class);
        }); 
    }
}
