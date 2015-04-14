<?php namespace DMA\Friends\Components;

use Cms\Classes\ComponentBase;
use DMA\Friends\Models\Activity;
use DMA\Friends\Models\Category;
//use Auth;
use DB;

class ActivityCatalog extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Activity Catalog',
            'description' => 'Allows the user to explore a full list of available activities',
        ];
    }

    public function onRun()
    {
        $this->getResults();
    }

    public function onUpdate()
    {
        $filters = post('filters');
        $this->getResults($filters);

        return [
            '#activity-catalog' => $this->renderPartial('@activitylist'),
        ];
    }

    private function getResults($filter = null)
    {
        $perpage = 10;

        if ($filter && $filter != 'all') {
            $filters = json_decode($filter, true);
            if (is_array($filters['categories'])) {
                $results = Activity::isActive()->byCategory($filters['categories'])->paginate($perpage);
            }
            else {
                $results = Activity::isActive()->paginate($perpage);
            }
        }
        else {
            $results = Activity::isActive()->paginate($perpage);
        }

        $this->page['activities'] = $results;
        $this->page['links'] = $results->links();
    }
}