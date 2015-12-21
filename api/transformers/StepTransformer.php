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
    protected $avilablesIncludes = [
            'activity',
            'badge'
    ];
    
    /**
     * @param boolean $useExtendedData 
     */
    public function __construct($useExtendedData=null, $excludeEmbededs=[], $user=null)
    {
        // If User is giving the transformer will check
        // if the user has completed the current step
        $this->user = $user;
        parent::__construct($useExtendedData, $excludeEmbededs);
    }
    
    
    /**
     * Step definition
     * @SWG\Definition(
     *    definition="step",
     *    required={"id", "title", "count"},
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
     *         property="count",
     *         type="integer",
     *         format="int32"
     *    ),
     *    @SWG\Property(
     *         property="activity",
     *         type="object",
     *         ref="#/definitions/activity"
     *    ),
     *    @SWG\Property(
     *         property="badge",
     *         type="object",
     *         ref="#/definitions/badge"
     *    )            
     * )
     */

    public function getData($instance)
    {
        return [
            'id'         => (int)$instance->id,
            'title'      => $instance->title,
            'count'      => $instance->count
        ];
    
    }

    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\API\BaseTransformer::getExtendedData()
     */
    public function getExtendedData($instance)
    {
        // Adding steps by the Fractal embeding system
        $this->setDefaultIncludes( array_merge($this->getDefaultIncludes(), ['activity', 'badge']));   
        
        if($this->user){
            $completed = \DMA\Friends\Models\Step::whereHas('users', 
                function($q) use ($instance)
                {
                    $q->where('user_id', $this->user->getKey());
                    $q->where('step_id', $instance->getKey());
                
                })->count() > 0;
            
            return [
                    'completed' => $completed
            ];
        }
    }
    
    /**
     * Include Steps
     *
     * @return League\Fractal\CollectionResource
     */
    public function includeActivity(Model $instance)
    {
        if($activity = $instance->activity){   
            $excludeEmbeds = [];//['categories'];
            $resource = $this->item($activity, new ActivityTransformer(false, $excludeEmbeds));
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


