<?php namespace DMA\Friends\API\Transformers;

use Model;
use Response;

use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\UserMetadataTransformer;

class CountryTransformer extends BaseTransformer {
    
   
    /**
     * @SWG\Definition(
     *    definition="country",
     *    required={"id", "name", "code", "is_enabled"},
     *    @SWG\Property(
     *         property="id",
     *         type="integer",
     *         format="int32"
     *    ),
     *    @SWG\Property(
     *         property="name",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="code",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="is_enabled",
     *         type="boolean",
     *    ),
     *    
     * )
     */
    
    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\API\BaseTransformer::getData()
     */
    
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
