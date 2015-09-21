<?php namespace DMA\Friends\API\Transformers;

use Model;
use Response;

use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\UserMetadataTransformer;

class LocationTransformer extends BaseTransformer {
    
    /**
     * Location definition
     * @SWG\Definition(
     *    definition="location",
     *    required={"id", "title", "description", "printer_reward", "printer_membership","uuid", "boolean", "image_url"},
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
     *         property="description",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="printer_reward",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="printer_membership",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="uuid",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="is_authorized",
     *         type="boolean",
     *    ),
     *    @SWG\Property(
     *         property="image_url",
     *         type="string",
     *    )
     * )
     */
    
    public function getData($instance)
    {
        $data = [
            'id'                    => intval($instance->id), 
            'title'                 => $instance->title, 
            'description'           => $instance->description, 
            'printer_reward'        => $instance->printer_reward,
            'printer_membership'    => $instance->printer_membership,
            'uuid'                  => $instance->uuid,
            'is_authorized'         => ($instance->is_authorized)?true:false,
            'image_url'             => $this->getImageUrl($instance),
        ];
        
        return $data;
    }

    
    /**
     * Get Image URL of the reward
     * @param unknown $instance
     */
    protected function getImageUrl(Model $instance)
    {
        try{
            if (!is_null($instance->image)) {
                return $instance->image->getPath();
            }
        }catch(\Exception $e){
            // Do nothing
        }
        return null;
    }
    
}
