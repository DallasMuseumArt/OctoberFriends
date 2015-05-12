<?php

namespace DMA\Friends\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use DMA\Friends\Models\Reward;
use DB;
use Cache;

class TopRewards extends ReportWidgetBase
{
    public $defaultAlias = 'TopRewards';

    /**
     * {@inheritDoc}
     */
    public function widgetDetails()
    {   
        return [
            'name'        => 'Top Rewards',
            'description' => 'Show highest ranking rewards'
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

        $rewards = Cache::remember('friends.report.topreward', GraphReport::getCacheTime(), function() use ($limit) {
            return DB::select(
                DB::raw("
                    SELECT 
                        reward.title, 
                        (
                            SELECT count(user_id)
                            FROM 
                                dma_friends_reward_user pivot
                            WHERE
                                pivot.reward_id = reward.id
                        ) as count
                    FROM dma_friends_rewards reward
                    LEFT JOIN dma_friends_reward_user pivot ON reward.id = pivot.reward_id
                    WHERE
                        pivot.created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND NOW()
                    GROUP BY pivot.reward_id
                    ORDER BY count DESC
                    LIMIT " . $limit
                )
            );
        });

        $this->vars['rewards'] = $rewards;

        return $this->makePartial('widget');
    }   
}

