<?php namespace DMA\Friends\ReportWidgets;

use DB;
use Cache;

class UserReport extends GraphReport
{
    public $defaultAlias = 'UsersReport';

    protected $widgetTitle = "User Report";

    /**
     * {@inheritDoc}
     */
    public function widgetDetails()
    {
        return [
            'name'        => 'Users',
            'description' => 'Show some basic statistics on friends'
        ];
    }

    static public function generateData()
    {
        // New users
        $query = DB::table('users')
                ->select(
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') AS Day"), 
                    DB::raw("COUNT('id') AS newUsers")
                )
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"))
                ->orderBy('Day', 'ASC');

        $newUsers = self::processQuery($query, 'created_at', 1000, 'friends.reports.newUsers');

        // Total users
        $query = DB::table('dma_friends_activity_user')
                ->select(
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') AS Day"), 
                    DB::raw("count(DISTINCT(user_id)) AS totalUsers")
                )
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"))
                ->orderBy('Day', 'ASC');

        $totalUsers = self::processQuery($query, 'created_at', 1000, 'friends.reports.totalUsers');

        // Organize the data into the proper format for C3.js
        $time               = ['x'];
        $newUsersData       = ['newUsers'];
        $totalUsersData     = ['totalUsers'];

        foreach($newUsers as $nu) {
            $data[$nu->Day]['newUsers'] = $nu->newUsers;
        }

        foreach($totalUsers as $tu) {
            $data[$tu->Day]['totalUsers'] = $tu->totalUsers;
        }


        foreach ($data as $key => $value) {
            $time[]             = $key;
            $newUsersData[]     = isset($value['newUsers']) ? $value['newUsers'] : 0;
            $totalUsersData[]   = isset($value['totalUsers']) ? $value['totalUsers'] : 0;
        }

        return [
            $time,
            $newUsersData,
            $totalUsersData,
        ];
    }

}