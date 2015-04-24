<?php namespace DMA\Friends\API\Transformers;

use Model;
use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\MediaTransformer;
use DMA\Friends\API\Transformers\StepTransformer;
use DMA\Friends\API\Transformers\DateTimeTransformerTrait;

class BadgeTransformer extends BaseTransformer {

     use DateTimeTransformerTrait;

     /**
      * List of default resources to include
      *
      * @var array
      */
     protected $defaultIncludes = [
             'media'
     ];     
        
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
            'steps'
    ];
    
   
    
    public function getData($instance)
    {
        return [
            'id'                        => (int)$instance->id,
            'title'                     => $instance->title, 
        ];
    }


    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\API\BaseTransformer::getExtendedData()
     */
    public function getExtendedData($instance)
    {
        // Adding steps by the Fractal embeding system
        $this->setDefaultIncludes( array_merge($this->getDefaultIncludes(), ['steps']));
    
        return [
                'description'               => $instance->description,
                'excerpt'                   => $instance->excerpt,
                'congratulations_text'      => $instance->congratulations_text,
                'maximum_earnings'          => $instance->maximum_earnings,
                'points'                    => $instance->points,
                'is_published'              => ($instance->is_published)?true:false,
                'is_archived'               => ($instance->is_archived)?true:false,
                'is_sequential'             => ($instance->is_sequential)?true:false,
                'show_earners'              => ($instance->show_earners)?true:false,
                'is_hidden'                 => ($instance->is_hidden)?true:false,
                'time_between_steps_min'    => $instance->time_between_steps_min,
                'time_between_steps_max'    => $instance->time_between_steps_max,
                'maximium_time'             => $instance->maximium_time,
                'special'                   => ($instance->special)?true:false,
                'time_restrictions'         => $this->getTimeRestrictions($instance),
        ];
    
    }
    
    /**
     * Helper method to deserialize time_restiction data
     * @param Model $instance
     * @return array
     */
    protected function getTimeRestrictions(Model $instance) 
    {
    
        $restrictions = [];
        $keys         = ['date_begin', 'date_end'];
    
        // Reset values
        foreach($keys as $key){
            $restrictions[$key] = null;
        }
    
        $restrictions['date_begin'] = $this->carbonToIso($instance->date_begin);
        $restrictions['date_end']   = $this->carbonToIso($instance->date_end);
    
        return $restrictions;
    }
    
       
    /**
     * Include Steps
     *
     * @return League\Fractal\CollectionResource
     */
    public function includeSteps(Model $instance)
    {
        $steps = $instance->steps;
        // No necessary to show steps activities given that the parent is an activity
        $exclude = ['badge'];
        return $this->collection($steps, new StepTransformer(true, $exclude));
    }


    /**
     * Include Media
     *
     * @return League\Fractal\ItemResource
     */
    public function includeMedia(Model $instance)
    {
        return $this->item($instance, new MediaTransformer);
    }
    
}
