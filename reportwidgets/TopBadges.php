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

        $query = DB::table('dma_friends_badges')
                ->select("title", DB::raw("count(dma_friends_badge_user.user_id) as count"))
                ->join("dma_friends_badge_user", 'dma_friends_badges.id', '=', 'dma_friends_badge_user.badge_id')
                ->groupBy("dma_friends_badge_user.badge_id")
                ->orderBy('count', 'DESC');

        $badges = GraphReport::processQuery($query, 'dma_friends_badge_user.created_at', $limit, 'friends.reports.topBadges');

        $this->vars['badges'] = $badges;

        return $this->makePartial('widget');
    }   
}

