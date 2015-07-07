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


