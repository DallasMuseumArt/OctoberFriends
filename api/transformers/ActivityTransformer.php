<?php namespace DMA\Friends\API\Transformers;

use Model;
use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\MediaTransformer;
use DMA\Friends\API\Transformers\CategoryTransformer;
use DMA\Friends\API\Transformers\DateTimeTransformerTrait;


class ActivityTransformer extends BaseTransformer {
    
    use DateTimeTransformerTrait;
    
    
    /**
     * List of default resources to include
     *
     * @var array
     */
    protected $defaultIncludes = [
            'media',
            'categories'
    ];
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $avilableIncludes = [
            'steps'
    ];

    /**
     * Activity definition
     * @SWG\Definition(
     *    definition="activity",
     *    type="object",
     *    required={"id", "title", "activity_code", "activity_type", "media", "categories"},
     *    @SWG\Property(
     *         property="id",
     *         type="integer",
     *         format="int32"
     *    ),
     *    @SWG\Property(
     *         property="title",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="activity_code",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="activity_type",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="media",
     *         type="object",
     *         ref="#/definitions/media"
     *    ),
     *    @SWG\Property(
     *         property="categories",
     *         type="array",
     *         items="#/definitions/category"
     *    )
     * )
     */
   
      
    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\API\BaseTransformer::getData()
     */
    public function getData($instance)
    {
        return [
                'id'                => (int)$instance->id,
                'title'             => $instance->title,
                'activity_code'     => $instance->activity_code,
                'activity_type'     => $instance->activity_type,
                //'feedback_message'  => $instance->feedback_message,
                //'complete_message'  => $instance->complete_message
        ];
    }

    
    // TODO : The swagger notation should be done using JSON Schema Polymorphism. 
    
    /**
     * Activity definition
     * @SWG\Definition(
     *    definition="activity.extended",
     *    type="object",
     *    required={"id", "title", "activity_code", "activity_type", "media", 
     *              "categories", "is_published", "is_archived", "steps"},
     *    @SWG\Property(
     *         property="id",
     *         type="integer",
     *         format="int32"
     *    ),
     *    @SWG\Property(
     *         property="title",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="activity_code",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="activity_type",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="media",
     *         type="object",
     *         ref="#/definitions/media"
     *    ),
     *    @SWG\Property(
     *         property="categories",
     *         type="array",
     *         items="#/definitions/category",
     *    ),
     *    @SWG\Property(
     *         property="is_published",
     *         type="boolean",
     *    ),
     *    @SWG\Property(
     *         property="is_archived",
     *         type="boolean",
     *    ),
     *    @SWG\Property(
     *         property="time_restrictions",
     *         type="object",
     *         ref="#/definitions/activity.restrictions"
     *    ),
     *    @SWG\Property(
     *         property="steps",
     *         type="array",
     *         items="#/definitions/step"
     *    ) 
     * )
     */
        
    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\API\BaseTransformer::getExtendedData()
     */
    public function getExtendedData($instance)
    {
        // Adding steps by the Fractal embeding system
        $this->setDefaultIncludes( array_merge($this->getDefaultIncludes(), ['steps']));
    
        return [
            'is_published'      => ($instance->is_published)?true:false,
            'is_archived'       => ($instance->is_archived)?true:false,
            'time_restrictions' => $this->getTimeRestrictions($instance),
        ];
    }
    
    
    /**
     * 
     * @SWG\Definition(
     *     definition="week_days",
     *     type="object",
     *     required={"monday","tuesday","wednesday", "thursday", "friday", "saturday", "sunday"},
     *     @SWG\Property(
     *          property="monday",
     *          type="boolean",
     *     ),
     *     @SWG\Property(
     *          property="tuesday",
     *          type="boolean",
     *     ),
     *     @SWG\Property(
     *          property="wednesday",
     *          type="boolean",
     *     ),
     *     @SWG\Property(
     *          property="thursday",
     *          type="boolean",
     *     ),  
     *     @SWG\Property(
     *          property="friday",
     *          type="boolean",
     *     ),        
     *     @SWG\Property(
     *          property="saturday",
     *          type="boolean",
     *     ),     
     *     @SWG\Property(
     *          property="sunday",
     *          type="boolean",
     *     )
     * )  
     * 
 
     * Activity definition
     * @SWG\Definition(
     *    definition="activity.restrictions",
     *    type="object",
     *    required={"date_begin", "date_end", "start_time", "end_time", "days",
     *              "type_range", "type_day"},
     *    @SWG\Property(
     *         property="date_begin",
     *         type="string",
     *         format="date-time",
     *    ),
     *    @SWG\Property(
     *         property="date_end",
     *         type="string",
     *         format="date-time",
     *    ),
     *    @SWG\Property(
     *         property="start_time",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="end_time",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="days",
     *         type="object",
     *         ref="#/definitions/week_days"
     *    ),
     *    @SWG\Property(
     *         property="type_range",
     *         type="boolean",
     *    ),
     *    @SWG\Property(
     *         property="type_day",
     *         type="boolean",
     *    )
     * )    
     */
        
    
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
     * Include Steps
     *
     * @return League\Fractal\CollectionResource
     */
    public function includeSteps(Model $instance)
    {
        $steps = $instance->steps;
        // No necessary to show steps activities given that the parent is an activity
        $exclude = ['activity']; 
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

    /**
     * Include Media
     *
     * @return League\Fractal\CollectionResource
     */
    public function includeCategories(Model $instance)
    {
        return $this->collection($instance->categories, new CategoryTransformer(false));
    }
    
    
}
