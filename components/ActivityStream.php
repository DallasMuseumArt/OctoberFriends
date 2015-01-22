<?php namespace DMA\Friends\Components;

use Cms\Classes\ComponentBase;
use October\Rain\Database\DataFeed;
use DMA\Friends\Models\ActivityLog;
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

        $results = $this->getResults();

        $this->page['results'] = $results;
        $this->page['links'] = $results->links();
    }

    public function onUpdate()
    {
        $filter = post('filter');
        $results = $this->getResults($filter);
        $this->page['results'] = $results;
        $this->page['links'] = $results->links();

        return [
            '#activity-stream'  => $this->renderPartial('@default'),
        ];
    }

    private function getResults($filter = null)
    {
        $user = Auth::getUser();

        if (!$user) return;
        
        $results = ActivityLog::byUser($user->id)
            ->whereIn('action', ['activity', 'artwork', 'reward', 'unlocked']);

        if ($filter && $filter != 'all') {
            $results = $results->where('action', $filter);
        }

        $results->orderBy('timestamp', 'desc');
        return $results->paginate(10);
    }
}