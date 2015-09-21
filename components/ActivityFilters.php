<?php namespace DMA\Friends\Components;

/**
 * [ActivityCatalog]
 * 
 * [ActivityFilters]
 * target_component = "ActivityCatalog"
 * ==
 * ==
 * {% component 'ActivityFilters' %}
 * {% component 'ActivityCatalog' %}
 */

use Cms\Classes\ComponentBase;
use DMA\Friends\Models\Category;
use DB;

class ActivityFilters extends ComponentBase
{
    /**
     * {@inheritDoc}
     */
    public function componentDetails()
    {
        return [
            'name' => 'Activity Filters',
            'description' => 'Provides an interface for filtering a list of activities'
        ];
    }

    /**
     * {@inheritDoc}
     */
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
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function onRun()
    {
        // Inject JS
        $this->addJs('components/activityfilters/assets/js/filter-activities.js');

        // Get category list for filters
        $filters = $this->getCategories();
        $this->page['categories'] = $filters;

        // Pass through component property to partial
        $this->page['component'] = $this->property('target_component') . '::onUpdate';
    }

    /**
     * Get a collection of all Categories
     */
    private function getCategories()
    {
        return Category::all();
    }
}