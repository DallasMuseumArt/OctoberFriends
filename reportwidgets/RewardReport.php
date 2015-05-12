<?php namespace DMA\Friends\ReportWidgets;

use DB;
use Cache;

class RewardReport extends GraphReport
{
    public $defaultAlias = 'RewardReport';

    protected $widgetTitle = "Rewards By Day";

    /**
     * {@inheritDoc}
     */
    public function widgetDetails()
    {
        return [
            'name'        => 'Rewards By Day',
            'description' => 'Show the number of rewards redeemed by day'
        ];
    }

    static public function generateData()
    {

        $rewards = Cache::remember('friends.reports.rewardReport', GraphReport::getCacheTime(), function() {
            return DB::select(
                DB::raw("
                    SELECT 
                        DATE_FORMAT(created_at, '%Y-%m-%d') AS Day,
                        COUNT('user_id') AS Count
                    FROM 
                        dma_friends_reward_user
                    WHERE 
                        created_at BETWEEN DATE_SUB(NOW(), INTERVAL 60 DAY) AND NOW()
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d')
                ")
            );
        });

        $time = ['x'];
        $data = ['count'];

        foreach($rewards as $value) {
            $time[] = $value->Day;
            $data[] = $value->Count;
        }

        return [
            $time,
            $data,
        ];

    }
}