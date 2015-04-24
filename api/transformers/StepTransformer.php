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
    protected $avilableIncludes = [
            'activity',
            'badge'
    ];
    
    public function getData($instance)
    {
        return [
            'id'         => (int)$instance->id,
            'title'      => $instance->title,
            'count'      => $instance->count
        ];

        // TODO : When using Fractal embeded system to include activity and badges it
        // has a great impact on performace. My debugging show that a series circular reference between
        // steps, badges and activities produce a chain reaction of nested loops, because an step has an activity and at the same time
        // activity has same or other steps. This happens as well with badges.
        // For that reason I skip the use on Fractal embeded system and build the structure manually.
        // This is not an ideal solution because this endpoint will be not benefict of having a common transformer.
        
        if (!is_null($activity = $instance->activity)){
           /* $data['activity'] = [   
                        'id' => $activity->getKey(),
                        'title' => $activity->title                   
            ];*/
        }
        
    
    }

    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\API\BaseTransformer::getExtendedData()
     */
    public function getExtendedData($instance)
    {
        // Adding steps by the Fractal embeding system
        $this->setDefaultIncludes( array_merge($this->getDefaultIncludes(), ['activity', 'badge']));
    }
    
    /**
     * Include Steps
     *
     * @return League\Fractal\CollectionResource
     */
    public function includeActivity(Model $instance)
    {
        if($activity = $instance->activity){   
            $resource = $this->item($activity, new ActivityTransformer(false));
            return $resource;
        }
    }

    /**
     * Include Steps
     *
     * @return League\Fractal\ItemResource
     */
    public function includeBadge(Model $instance)
    {
        if($badge = $instance->badge){
            return $this->item($badge, new BadgeTransformer(false));
        }
    }
    
}
