<?php namespace DMA\Friends\Components;

use Cms\Classes\ComponentBase;
use DMA\Friends\Models\Activity;
use DMA\Friends\Models\Category;
//use Auth;
use DB;

class ActivityCatalog extends ComponentBase
{
    /**
     * {@inheritDoc}
     */
    public function componentDetails()
    {
        return [
            'name' => 'Activity Catalog',
            'description' => 'Allows the user to explore a full list of available activities',
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function defineProperties()
    {
        return [
            'containerclass' => [
                'title'             => 'Container Class',
                'description'       => 'Optional CSS class for Activity List container div',
                'type'              => 'string',
                'default'           => '',
                'validationPattern' => '^[a-zA-Z_- ]*$',
                'validationMessage' => 'Class must be a valid CSS class identifier.',
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function onRun()
    {
        $this->getResults();
        $this->page['containerclass'] = $this->property('containerclass');
    }

    /**
     * {@inheritDoc}
     */
    public function onUpdate()
    {
        $filters = post('filters');
        $this->getResults($filters);

        // Render only the activitylist partial and not the full default partial
        // Avoids AJAX producing a load of nested div#activity-catalog elements
        return [
            '#activity-catalog' => $this->renderPartial('@activitylist'),
        ];
    }

    /**
     * Produce a collection of Activities based on recommendations and filters
     */
    private function getResults($filterstr = null)
    {
        $perpage = 10;

        if ($filterstr && $filterstr != 'all') {
            $filters = json_decode($filterstr, true);
            if ($filters && is_array($filters['categories'])) {
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