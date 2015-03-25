<?php namespace DMA\Friends\Components;

/**
 * You don't need to specifiy component properties 90% of the time, but if you do
 * here's an example use of this component with properties:
 * [ActivityCatalog]
 * 
 * [ActivityFilters]
 * target_component = "ActivityCatalog"
 * target_element = "'@default': '#activity-catalog'"
 * ==
 * ==
 * {% component 'ActivityCatalog' %}
 * {% component 'ActivityFilters' %}
 */

use Cms\Classes\ComponentBase;
use DMA\Friends\Models\Category;
//use Auth;
use DB;

class ActivityFilters extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Activity Filters',
            'description' => 'Provides an interface for filtering a list of activities'
        ];
    }

    public function defineProperties()
    {
        return [
            'target_component'  => [
                 'title'             => 'Target Component',
                 'description'       => 'The component producing the activity list to be filtered',
                 'default'           => '',
                 'type'              => 'string',
                 'validationPattern' => '^[a-zA-Z_]*$',
                 'validationMessage' => 'The target component must be a string reference to a valid component.',
            ],
            'target_element'    => [
                'title'                 => 'Target Element',
                'description'           => 'The DOM element to replace',
                'default'               => '',
                'type'                  => 'string',
            ],
            'partial'           => [
                'title'                 => 'Partial',
                'description'           => 'The partial to use when updating',
                'default'               => '',
                'type'                  => 'string',
            ],
        ];
    }

    public function onRun()
    {
        // Inject JS
        $this->addJs('components/activityfilters/assets/js/filter-activities.js');

        // Get category list for filters
        $filters = $this->getCategories();
        $this->page['categories'] = $filters;

        // Pass properties through to partial
        if ($this->property('target_component') == '') {
            $this->page['component'] = '';
        }
        else {
            $this->page['component'] = $this->property('target_component') . '::onUpdate';
        }
        $this->page['element'] = $this->property('target_element');
        $this->page['partial'] = $this->property('partial');
    }

    private function getCategories()
    {
        $results = Category::all();

        return $results;
    }
}