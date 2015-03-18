<?php namespace DMA\Friends\API\Transformers;

use Model;
use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\StepTransformer;

class BadgeTransformer extends BaseTransformer {


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
            'id'                        => (int)$instance->id,
            'title'                     => $instance->title,
            'description'               => $instance->description, 
            'excerpt'                   => $instance->excerpt,
            'congratulations_text'      => $instance->congratulations_text,
            'is_published'              => $instance->is_published,
            'is_archived'               => $instance->is_archived,
            'maximum_earnings'          => $instance->maximum_earnings,
            'points'                    => $instance->image_id,
            'image_id'                  => $instance->is_sequential,
            'is_sequential'             => $instance->is_sequential,
            'show_earners'              => $instance->show_earners,
            'is_hidden'                 => $instance->is_hidden,
            'time_between_steps_min'    => $instance->time_between_steps_min,
            'time_between_steps_max'    => $instance->time_between_steps_max,
            'maximium_time'             => $instance->maximium_time,
            'date_begin'                => $instance->date_begin,
            'date_end'                  => $instance->date_end,
            'special'                   => $instance->special             
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

        return $this->collection($steps, new StepTransformer);
    }

}
