<?php namespace DMA\Friends\API\Transformers;

use Model;

use DMA\Friends\Classes\API\BaseTransformer;

class UserTransformer extends BaseTransformer {
    

    public function getData($instance)
    {
        return [
            'id'   => (int)$instance->id,
            'name' => $instance->metadata->first_name,
        ];
    }


    
}
