<?php namespace DMA\Friends\API\Transformers;

use Model;

use DMA\Friends\Classes\API\BaseTransformer;

class ActivityTransformer extends BaseTransformer {
    
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $defaultIncludes = [
            //'steps'
    ];
    
    public function getData($instance)
    {
        return [
            'id'    => (int)$instance->id,
            'title' => $instance->title,
        ];
    }

    /**
     * Include Steps
     *
     * @return League\Fractal\CollectionResource
     */
    public function includeSteps(Model $instance)
    {
 
        $steps = $instance->steps;
        return $this->collection($steps, new StepTransformer(false));
    }


    
}
