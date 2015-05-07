<?php namespace DMA\Friends\ReportWidgets;

use DB;

class UserReport extends GraphReport
{
    public $defaultAlias = 'UsersReport';

    protected $widgetTitle = "Users";

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
                    COUNT('id') AS Count
                FROM 
                    users
                WHERE 
                    created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND NOW()
                GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
                ORDER BY Day ASC LIMIT 1000;
            ")
        );

        // Organize the data into the proper format for C3.js
        $time = ['x'];
        $dataPoint = ['data1'];

        foreach ($newUsers as $value) {
            $time[]         = $value->Day;
            $dataPoint[]    = $value->Count;
        }

        return [
            $time,
            $dataPoint,
        ];
    }

}