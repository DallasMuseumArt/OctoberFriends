<?php namespace DMA\Friends\API\Transformers;

use Model;
use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\DateTimeTransformerTrait;

class ActivityTransformer extends BaseTransformer {
    
    use DateTimeTransformerTrait;
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $avilableIncludes = [
            'steps'
    ];
    
    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\API\BaseTransformer::getData()
     */
    public function getData($instance)
    {
        return [
                'id'                => (int)$instance->id,
                'title'             => $instance->title,
                'image_url'         => $this->getImageUrl($instance),
                'activity_code'     => $instance->activity_code,
                'activity_type'     => $instance->activity_type,   
        ];
    }

    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\API\BaseTransformer::getExtendedData()
     */
    public function getExtendedData($instance)
    {
        // Adding steps by the Fractal embeding system
        $this->setDefaultIncludes(['steps']);
    
        return [
            'is_published'      => ($instance->is_published)?true:false,
            'is_archived'       => ($instance->is_archived)?true:false,
            'time_restrictions' => $this->getTimeRestrictions($instance),
        ];
    }
    
    
    /**
     * Helper method to deserialize time_restiction data
     * @param Model $instance
     * @return array
     */
    protected  function getTimeRestrictions(Model $instance) 
    {
        $restrictions = [];
	    $keys         = ['date_begin', 'date_end', 'start_time', 'end_time', 'days'];
	    $dayNames     = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $type_range   = false;
        $type_day     = false;
	    
        $type         = $instance->time_restriction;
        $data         = $instance->time_restriction_data; // Get deserializated time restrictions data
        
        // Reset values
        foreach($keys as $key){
            if ($key != 'days') {
                $restrictions[$key] = null;
            } else {
                $restrictions[$key] = array_fill_keys($dayNames, false);
            }
        }

               
        switch($type){
            case 0:
                break;
            
            case 1: // Days / Hours
                $days = [];
                foreach($data['days'] as $key => $value){
                    $days[ $dayNames[$key-1] ] = $value;
                }
                $restrictions['days'] = $days;

                $restrictions['start_time'] = $this->normalizeTime($data['start_time']);
                $restrictions['end_time']   = $this->normalizeTime($data['end_time']);
                
                $type_day = true;
                
                break;
            
            case 2: // Date range
                $restrictions['date_begin'] = $this->carbonToIso($instance->date_begin, 'date');
                $restrictions['date_end']   = $this->carbonToIso($instance->date_end, 'date');
                
                $restrictions['start_time'] = $this->carbonToIso($instance->date_begin, 'time');
                $restrictions['end_time']   = $this->carbonToIso($instance->date_end, 'time');

                $type_range = true;
                
                break;
        }
        // Store type
	    //$restrictions['type']       = $type;
	    
	    $restrictions['type_range'] = $type_range;
	    $restrictions['type_day']   = $type_day;


	    return $restrictions;
    }
    

    /**
     * Get Image URL of the reward
     * @param unknown $instance
     */
    protected function getImageUrl(Model $instance)
    {
        try{
            if (!is_null($instance->image)) {
                return $instance->image->getPath();
            }
        }catch(\Exception $e){
            // Do nothing
        }
        return null;
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
