<?php namespace DMA\Friends\API\Transformers;

use Model;
use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\DateTimeTransformerTrait;
use DMA\Friends\API\Transformers\UserTransformer;


class RateTransformer extends BaseTransformer {
    
    use DateTimeTransformerTrait;
       
    
    protected $defaultIncludes = [
            'user'
    ];
      
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
        return $this->item($instance, new UserTransformer);
    }
    
}
