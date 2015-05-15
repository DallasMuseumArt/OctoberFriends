<?php

namespace DMA\Friends\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use DB;
use Cache;

class TopActivities extends ReportWidgetBase
{
    public $defaultAlias = 'TopActivities';

    /**
     * {@inheritDoc}
     */
    public function widgetDetails()
    {   
        return [
            'name'        => 'Top Activities',
            'description' => 'Show highest ranking activities'
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
                'default'           => 10,
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

        $query = DB::table('dma_friends_activities')
                ->select("title", DB::raw("count(dma_friends_activity_user.user_id) as count"))
                ->join("dma_friends_activity_user", 'id', '=', 'dma_friends_activity_user.activity_id')
                ->groupBy("dma_friends_activity_user.activity_id")
                ->orderBy('count', 'DESC');

        $activities = GraphReport::processQuery($query, 'dma_friends_activity_user.created_at', $limit, 'friends.reports.topActivities');

        $this->vars['activities'] = $activities;

        return $this->makePartial('widget');
    }   
}

