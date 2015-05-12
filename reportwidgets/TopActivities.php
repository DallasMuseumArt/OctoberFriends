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

        $activities = Cache::remember('friends.report.topactivities', GraphReport::getCacheTime(), function() use ($limit) {
            return DB::select(
                DB::raw("
                    SELECT 
                        activity.title, 
                        count(pivot.user_id) as count
                    FROM dma_friends_activities activity
                    LEFT JOIN dma_friends_activity_user pivot ON activity.id = pivot.activity_id
                    WHERE
                        pivot.created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND NOW()
                    GROUP BY pivot.activity_id
                    ORDER BY count DESC
                    LIMIT " . $limit
                )
            );
        });

        $this->vars['activities'] = $activities;

        return $this->makePartial('widget');
    }   
}

