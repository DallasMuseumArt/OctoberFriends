<?php namespace DMA\Friends\ReportWidgets;

use DB;
use Cache;

class ActivitiesByDay extends GraphReport
{
    public $defaultAlias = 'ActivitiesByDay';

    protected $widgetTitle = "Activities By Day";

    /**
     * {@inheritDoc}
     */
    public function widgetDetails()
    {
        return [
            'name'        => 'Activities By Day',
            'description' => 'Show some basic statistics on friends'
        ];
    }

    static public function generateData()
    {
        $data = Cache::remember('friends.reports.activitiesByDay', GraphReport::getCacheTime(), function() {
            return DB::select(
                DB::raw("
                    SELECT 
                        DATE_FORMAT(created_at, '%Y-%m-%d') AS Day,
                        COUNT('id') AS Count
                    FROM
                        dma_friends_activity_user
                    WHERE
                        created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND NOW()
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
                    ORDER BY Day ASC
                    LIMIT 1000;
                ")
            );
        });

        // Organize the data into the proper format for C3.js
        $time = ['x'];
        $dataPoint = ['data'];

        foreach ($data as $value) {
            $time[] = $value->Day;
            $dataPoint[] = $value->Count;
        }

        return [
            $time,
            $dataPoint,
        ];
    }
}