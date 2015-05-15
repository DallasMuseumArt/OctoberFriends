<?php namespace DMA\Friends\ReportWidgets;

use DB;

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

        $query = DB::table('dma_friends_activity_user')
                ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') AS Day"), DB::raw("COUNT('id') AS Count"))
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"))
                ->orderBy('Day', 'ASC');

        $data = self::processQuery($query, 'created_at', 1000, 'friends.reports.activitiesByDay');

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