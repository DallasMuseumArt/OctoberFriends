<?php namespace DMA\Friends\API\Transformers;

use Model;
use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\DateTimeTransformerTrait;
use DMA\Friends\API\Transformers\UserTransformer;


class RatingTransformer extends BaseTransformer {
    
    use DateTimeTransformerTrait;
       
    
    protected $defaultIncludes = [
            'user'
    ];
      
  /**
     * @SWG\Definition(
     *    definition="rate",
     *    description="Rate definition",
     *    required={"id", "rate", "comment", "create_at"},
     *    @SWG\Property(
     *         property="id",
     *         type="integer",
     *         format="int32"
     *    ),
     *    @SWG\Property(
     *         property="rate",
     *         type="number",
     *         format="float",
     *         maximum=5,
     *         minimum=1 
     *    ),
     *    @SWG\Property(
     *         property="comment",
     *         type="string",
     *    ),    
     *    @SWG\Property(
     *         property="create_at",
     *         type="string",
     *         format="date-time"
     *    ),
     *    @SWG\Property(
     *         property="user",
     *         type="object",
     *         ref="#/definitions/user"
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
                'rate'              => $instance->rate,
                'comment'           => $instance->comment,   
                'created_at'        => $this->carbonToIso($instance->created_at),
        ];
    }  
        
    /**
     * Include Media
     *
     * @return League\Fractal\ItemResource
     */
    public function includeUser(Model $instance)
    {
        $user = $instance->user;
        return $this->item($user, new UserTransformer);
    }
    
}
