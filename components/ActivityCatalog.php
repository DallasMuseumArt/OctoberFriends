<?php namespace DMA\Friends\Components;

use Cms\Classes\ComponentBase;
use DMA\Friends\Models\Activity;
use DMA\Friends\Models\Category;
//use Auth;
use DB;

class ActivityCatalog extends ComponentBase
{
    public function componentDetails() {
        return [
            'name' => 'Activity Catalog',
            'description' => 'Allows the user to explore a full list of available activities',
        ];
    }

    public function onRun() {
        $this->getResults();
    }

    public function onUpdate() {
        $filter = post('filter');
        $this->getResults($filter);

        return [
            '#activity-catalog' => $this->renderPartial('@default'),
        ];
    }

    private function getResults($filter = null) {
        if ($filter && $filter != 'all') {
            //$filters = preg_split('/(\s*,\s*)+/', $filter);
            $filters = explode(',', $filter);
            $results = Activity::isActive()->byCategory($filters)->paginate(10);
        }
        else {
            $results = Activity::isActive()->paginate(10);
        }

        $this->page['activities'] = $results;
        $this->page['links'] = $results->links();
    }
}