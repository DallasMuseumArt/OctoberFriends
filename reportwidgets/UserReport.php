<?php namespace DMA\Friends\ReportWidgets;

use DB;

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
       $newUsers = DB::select(
            DB::raw("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m-%d') AS Day,
                    COUNT('id') AS newUsers
                FROM 
                    users
                WHERE 
                    created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND NOW()
                GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
            ")
        );

        $totalUsers = DB::select(
            DB::raw("
                SELECT 
                    DATE_FORMAT(created_at, '%Y-%m-%d') AS Day,
                    count(DISTINCT(user_id)) AS totalUsers
                FROM 
                    dma_friends_activity_user
                WHERE 
                    created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND NOW()
                GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
            ")
        );

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