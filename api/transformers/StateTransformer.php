<?php namespace DMA\Friends\API\Transformers;

use Model;
use Response;

use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\UserMetadataTransformer;

class StateTransformer extends BaseTransformer {
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $defaultIncludes = [
            //'country'
    ];
    
    public function getData($instance)
    {
        $data = [
            'id'         => $instance->id, 
            'name'       => $instance->name, 
            'code'       => $instance->code
        ];
        
        return $data;
    }

    

    /**
     * Include Country
     *
     * @return League\Fractal\ItemResource
     */
    public function includeCountry(Model $instance)
    {
    
        $country = $instance->country;
        return $this->item($country, new CountryTransformer);
    }
    
    
    
}
