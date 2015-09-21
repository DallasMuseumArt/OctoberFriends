<?php namespace DMA\Friends\API\Transformers;

use Model;
use Response;

use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\UserMetadataTransformer;

class CategoryTransformer extends BaseTransformer {
    
   
    /**
     * Category definition
     * @SWG\Definition(
     *    definition="category",
     *    required={"id", "name", "description", "slug"},
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
     *         property="description",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="slug",
     *         type="string",
     *    )
     * )
     */
    
    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\API\BaseTransformer::getData()
     */
    
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
