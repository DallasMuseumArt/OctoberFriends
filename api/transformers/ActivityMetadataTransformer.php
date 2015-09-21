<?php namespace DMA\Friends\API\Transformers;

use Model;
use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\ActivityTransformer;
use DMA\Friends\API\Transformers\UserTransformer;
use DMA\Friends\API\Transformers\DateTimeTransformerTrait;

class ActivityMetadataTransformer extends BaseTransformer {
    
    use DateTimeTransformerTrait;
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $defaultIncludes = [
            'user',
            'activity'
    ];
    
    /**
     * @SWG\Definition(
     *    definition="activity.metadata",
     *    description="Activity Metadata definition",
     *    required={"id", "user", "activity", "metadata", "created_at"},
     *    @SWG\Property(
     *         property="id",
     *         type="integer",
     *         format="int32"
     *    ),
     *    @SWG\Property(
     *         property="user",
     *         type="object",
     *         ref="#/definitions/user"
     *    ),
     *    @SWG\Property(
     *         property="activity",
     *         type="object",
     *         ref="#/definitions/activity"
     *    ),
     *    @SWG\Property(
     *         property="metadata",
     *         type="object",
     *    ),
     *    @SWG\Property(
     *         property="created_at",
     *         type="string",
     *         format="date-time"
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
            'id'           => (int)$instance->id,
            'user'         => null,
            'activity'     => null,
            'metadata'     => $this->getMetadata($instance),   
            'created_at'   => $this->carbonToIso($instance->created_at)
            //'session_id'   => $instance->session_id,
            
        ];
    }
        
    /**
     * Get metadata values
     * @return array
     */
    public function getMetadata(Model $instance)
    {
        // TODO : this method needs to be improved 
        // This method generates one extra query per row 
        // transformed.
        
        $class = get_class($instance);
        $query = $class::where('session_id', $instance->session_id)->get();
         
        $data = [];
        foreach($query as $r){
            $data[$r->key] = $r->value;
        }
    
        return $data;
    }
    
    
    /**
     * Include Activity
     *
     * @return League\Fractal\ItemResource
     */
    public function includeActivity(Model $instance)
    {
 
        $activity = $instance->activity;
        return $this->item($activity, new ActivityTransformer(false));
    }

    /**
     * Include User
     *
     * @return League\Fractal\ItemResource
     */
    public function includeUser(Model $instance)
    {
    
        $instance = $instance->user;
        return $this->item($instance, new UserTransformer(false));
    }

  
}
