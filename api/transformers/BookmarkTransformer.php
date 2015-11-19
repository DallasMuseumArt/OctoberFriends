<?php namespace DMA\Friends\API\Transformers;

use Model;
use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\ActivityTransformer;
use DMA\Friends\API\Transformers\BadgeTransformer;
use DMA\Friends\API\Transformers\RewardTransformer;
use DMA\Friends\API\Transformers\DateTimeTransformerTrait;
use DMA\Friends\Models\Badge;
use DMA\Friends\Models\Activity;
use DMA\Friends\Models\Reward;

class BookmarkTransformer extends BaseTransformer {

     use DateTimeTransformerTrait;

     /**
      * List of default resources to include
      *
      * @var array
      */
     protected $defaultIncludes = [
          'object'
     ];     
        
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [];
    
    /**
     * Badge definition
     * @SWG\Definition(
     *    definition="badge",
     *    required={"id", "title", "steps"},
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
     *         property="steps",
     *         type="array",
     *         items=@SWG\Schema(ref="#/definitions/step")
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
            'id'            => (int)$instance->id,
            'created_at'    => $this->carbonToIso($instance->created_at), 
            'object_type'   => $this->getObjectType($instance)
             
        ];
    }
    
    /**
     * @internal
     * @param Model $instance
     */
    private function getObjectType($instance)
    {
        return array_reverse(explode('\\', $instance->object_type))[0];
    }
    
    
    /**
     * Apply transformer to bookmarked object
     *
     * @return League\Fractal\ItemResource
     */
    public function includeObject(Model $instance)
    {
        $objectId = $instance->object_id;
        $model = $instance->object_type;
        
        $transformer = array_get([
                'Badge' => '\DMA\Friends\API\Transformers\BadgeTransformer',
                'Activity' => '\DMA\Friends\API\Transformers\ActivityTransformer',
                'Reward' => '\DMA\Friends\API\Transformers\RewardTransformer',
        ], $this->getObjectType($instance), 
                '\DMA\Friends\Classes\API\BaseTransformer');

        $object   = $model::find($objectId); 
        return $this->item($object, new $transformer(false));
    }

    
    
  
    
}
