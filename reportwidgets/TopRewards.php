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

        $query = DB::table('dma_friends_rewards')
                ->select("title", DB::raw("count(dma_friends_reward_user.user_id) as count"))
                ->join("dma_friends_reward_user", 'dma_friends_rewards.id', '=', 'dma_friends_reward_user.reward_id')
                ->groupBy("dma_friends_reward_user.reward_id")
                ->orderBy('count', 'DESC');

        $rewards = GraphReport::processQuery($query, 'dma_friends_reward_user.created_at', $limit, 'friends.reports.topRewards');

        $this->vars['rewards'] = $rewards;

        return $this->makePartial('widget');
    }   
}

