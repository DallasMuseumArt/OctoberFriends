<?php

namespace DMA\Friends\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use DB;
use Cache;

class TopBadges extends ReportWidgetBase
{
    public $defaultAlias = 'TopBadges';

    /**
     * {@inheritDoc}
     */
    public function widgetDetails()
    {   
        return [
            'name'        => 'Top Badges',
            'description' => 'Show highest ranking Badges'
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

        $badges = Cache::remember('friends.report.topbadges', GraphReport::getCacheTime(), function() use ($limit) {
            return DB::select(
                DB::raw("
                    SELECT 
                        badge.title, 
                        count(pivot.user_id) as count
                    FROM dma_friends_badges badge
                    LEFT JOIN dma_friends_badge_user pivot ON badge.id = pivot.badge_id
                    WHERE
                        pivot.created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND NOW()
                    GROUP BY pivot.badge_id
                    ORDER BY count DESC
                    LIMIT " . $limit
                )
            );
        });

        $this->vars['badges'] = $badges;

        return $this->makePartial('widget');
    }   
}

