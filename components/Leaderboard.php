<?php namespace DMA\Friends\Components;

use Cms\Classes\ComponentBase;
use RainLab\User\Models\User;
use DMA\Friends\Models\Usermeta;

class Leaderboard extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Leaderboard',
            'description' => 'Display the users with the most points'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $users = User::with(['metadata' => function($query) {
                $query->where('current_member', '!=', Usermeta::IS_STAFF);
            }])
            ->orderBy('points_today', 'desc')
            ->take(10)
            ->get();

        $this->page['users'] = $users;
        \Debugbar::info($users);
    }

}