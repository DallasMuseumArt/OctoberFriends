<?php

namespace DMA\Friends\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Rainlab\User\Models\User;
use DB;

class FriendsToolbar extends ReportWidgetBase
{
    public $defaultAlias = 'friendsToolbar';

    /**
     * {@inheritDoc}
     */
    public function widgetDetails()
    {
        return [
            'name'        => 'Friends Toolbar',
            'description' => 'Show some basic statistics on friends'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $today = date('Y-m-d');
        $thisWeek = date('Y-m-d', strtotime('last monday'));

        $this->addCss('css/friendstoolbar.css');

        $this->vars['numFriends']       = number_format(User::count());
        $this->vars['todayFriends']     = number_format(User::where('created_at', '>=', $today)->count());
        $this->vars['weekFriends']      = number_format(User::where('created_at', '>=', $thisWeek)->count());
        $this->vars['averageFriends']   = number_format($this->getAverageFriends());

        return $this->makePartial('widget');
    }

    public function getAverageFriends()
    {
        $average = DB::select(
            DB::raw("
                SELECT 
                    AVG(numFriends) as avgNum
                FROM
                    (
                        SELECT 
                            DAYOFWEEK(created_at) AS dow,
                            DATE(created_at) AS d,
                            COUNT(*) AS numFriends
                        FROM users
                        GROUP BY d
                    ) AS countFriends
                WHERE dow = DAYOFWEEK(NOW())
                GROUP BY dow
            ")
        );

        if (!empty($average)) {
            return $average[0]->avgNum;
        }
        return 0;
    }
}
