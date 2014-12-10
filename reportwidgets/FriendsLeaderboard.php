<?php

namespace DMA\Friends\ReportWidgets;

use App;
use Backend\Classes\ReportWidgetBase;
use Rainlab\User\Models\User;
use DMA\Friends\Classes\UserExtend;
//use DMA\Friends\Models\Usermeta;

class FriendsLeaderboard extends ReportWidgetBase
{
    public $defaultAlias = 'friendsLeaderboard';

    /**
     * {@inheritDoc}
     */
    public function widgetDetails()
    {   
        return [
            'name'        => 'Friends Leaderboard',
            'description' => 'Show highest ranking friends members by points'
        ];  
    }   

    /**
     * {@inheritDoc}
     */
    public function defineProperties()
    {
        return [
            'limit' => [
                'title'             => 'Number of results',
                'defualt'           => 10,
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$'
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {   
        $limit = $this->property('limit');

        $users = User::orderBy('points_today', 'DESC')->take($limit)->get();
        $this->vars['users'] = $users;

        return $this->makePartial('widget');
    }   
}

