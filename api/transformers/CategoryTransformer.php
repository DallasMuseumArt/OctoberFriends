<?php namespace DMA\Friends\API\Transformers;

use Model;
use Response;

use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\UserMetadataTransformer;

class CategoryTransformer extends BaseTransformer {
    
   
    public function getData($instance)
    {
        $data = [
            'id'            => intval($instance->id), 
            'name'          => $instance->name, 
            'description'   => $instance->description, 
            'slug'          => $instance->slug
        ];
        
        return $data;
    }

   
}
