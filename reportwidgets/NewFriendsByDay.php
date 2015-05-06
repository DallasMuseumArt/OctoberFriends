<?php namespace DMA\Friends\ReportWidgets;

use DB;

class NewFriendsByDay extends GraphReport
{
    public $defaultAlias = 'ChartFriendsByDay';

    /**
     * {@inheritDoc}
     */
    public function widgetDetails()
    {
        return [
            'name'        => 'Friends By Day',
            'description' => 'Show some basic statistics on friends'
        ];
    }

    public function render()
    {
        $this->addAssets();
        $data = $this->onGenerateData();
        return $this->makePartial('widget', ['data' => $data]);
    }

    public function onGenerateData()
    {
        $data = DB::select(
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