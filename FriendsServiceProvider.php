<?php

namespace DMA\Friends;

use Illuminate\Support\ServiceProvider;
use DMA\Friends\Classes\ActivityProcessor;
use DMA\Friends\Classes\BadgeProcessor;

class FriendsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerActivityProcessor();
        $this->registerBadgeProcessor();
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
}
