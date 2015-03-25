<?php namespace DMA\Friends\API\Transformers;

use Model;
use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\ActivityTransformer;

class StepTransformer extends BaseTransformer {


    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $defaultIncludes = [
            //'activity',
            //'badge'
    ];
    
    public function getData($instance)
    {
        return [
            'id'         => (int)$instance->id,
            'title'      => $instance->title,
            'count'      => $instance->count,
        ];
    }

    /**
     * Include Steps
     *
     * @return League\Fractal\CollectionResource
     */
    public function includeActivity(Model $instance)
    {
        $activity = $instance->activity;
        $resource = $this->item($activity, new ActivityTransformer);
        return $resource;
    }

    /**
     * Include Steps
     *
     * @return League\Fractal\ItemResource
     */
    public function includeBadge(Model $instance)
    {
        $badge = $instance->badge;
        return $this->item($badge, new BadgeTransformer);
    }
    
}
