<?php

namespace DMA\Friends\ReportWidgets;

use App;
use Backend\Classes\ReportWidgetBase;
use Rainlab\User\Models\User;
use DMA\Friends\Models\Usermeta;

class FriendsLeaderboard extends ReportWidgetBase
{
    public $defaultAlias = 'friendsLeaderboard';

    public function widgetDetails()
    {   
        return [
            'name'        => 'Friends Leaderboard',
            'description' => 'Show highest ranking friends members by points'
        ];  
    }   

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

    public function render()
    {   
        $limit = $this->property('limit');

        $users = Usermeta::byPoints()->take($limit)->get();
        $this->vars['users'] = $users;

        return $this->makePartial('widget');
    }   
}

