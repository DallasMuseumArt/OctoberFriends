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

        $query = DB::table('dma_friends_reward_user')
                ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') AS Day"), DB::raw("COUNT('user_id') AS Count"))
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d')"))
                ->orderBy('Day', 'ASC');

        $rewards = self::processQuery($query, 'created_at', 1000, 'friends.reports.rewardReport');

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