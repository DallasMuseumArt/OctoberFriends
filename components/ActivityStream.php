<?php namespace DMA\Friends\Components;

use Cms\Classes\ComponentBase;
use October\Rain\Database\DataFeed;
use DMA\Friends\Models\ActivityStream as ActivityStreamModel;
use Auth;
use DB;

class ActivityStream extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Activity Stream',
            'description' => 'Shows the users most recent'
        ];
    }

    public function onRun()
    {
        $user = Auth::getUser();

        if (!$user) return;

        $results = ActivityStreamModel::user($user->id)->get();

        \Debugbar::info($results);

        // $results = DB::query('dma_friends_badge_user')
        //     ->join('dma_friends_reward_user', 'dma_friends_reward_user.user_id', '=', 'dma_friends_badge_user.user_id')
        //     ->join('dma_friends_activity_user', 'dma_friends_activity_user.user_id', '=', 'dma_friends_badge_user.user_id')
        //     ->join('dma_friends_step_user', 'dma_friends_step_user.user_id', '=', 'dma_friends_badge_user.user_id')

        // $feed = new DataFeed;
        // // $feed->add('activity', $user->activities);
        // // $feed->add('badge', $user->badges);
        // // $feed->add('reward', $user->rewards);
        // $feed->add('activity', function() use ($user) {
        //     $activity = new \DMA\Friends\Models\Activity;
        //     return $activity->with('users', function($query) use ($user) {
        //         return $query->where('pivot_user_id', '=', $user->id);
        //     });
        // });

        // $feed->add('badge', function() use ($user) {
        //     $badge = new \DMA\Friends\Models\Badge;
        //     return $badge->users()->where('user_id', $user->id);
        // });
    

        $this->page['results'] = [];
        //$this->page['links'] = $feed->links();
    \Debugbar::info($this->page['results']);
    }
}