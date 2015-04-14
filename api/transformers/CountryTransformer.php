<?php namespace DMA\Friends\API\Transformers;

use Model;
use Response;

use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\UserMetadataTransformer;

class CountryTransformer extends BaseTransformer {
    
   
    public function getData($instance)
    {
        $data = [
            'id'         => $instance->id, 
            'name'       => $instance->name, 
            'code'       => $instance->code, 
            'is_enabled' => ($instance->is_enabled)?true:false, 
        ];
        
        return $data;
    }

    
}
