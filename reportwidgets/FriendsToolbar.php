<?php

namespace DMA\Friends\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Rainlab\User\Models\User;
use DMA\Friends\Models\Usermeta;
use DMA\Friends\ReportWidgets\GraphReport;
use DMA\Friends\Models\Settings as FriendsSettings;
use DB;
use Cache;

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

        $meta = new Usermeta;
        $meta_table = $meta->getTable();

        $this->vars['numFriends']       = Usermeta::where('current_member', '!=', Usermeta::IS_STAFF)->count();
        $this->vars['todayFriends']     = User::join($meta_table, 'users.id', '=', $meta_table . '.user_id')
            ->where('current_member', '!=', Usermeta::IS_STAFF) 
            ->where('created_at', '>=', $today)->count();

        $this->vars['weekFriends']      = User::join($meta_table, 'users.id', '=', $meta_table . '.user_id') 
            ->where('current_member', '!=', Usermeta::IS_STAFF)
            ->where('created_at', '>=', $thisWeek)->count();

        $this->vars['averageFriends']   = $this->getAverageFriends();

        return $this->makePartial('widget');
    }

    public function getAverageFriends()
    {
        
        $average = Cache::remember('friends.reports.toolbar', GraphReport::getCacheTime(), function() {
            return DB::select(
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
        });

        if (!empty($average)) {
            return $average[0]->avgNum;
        }
        return 0;
    }
}
