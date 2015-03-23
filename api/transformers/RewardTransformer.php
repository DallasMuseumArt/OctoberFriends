<?php namespace DMA\Friends\API\Transformers;

use Model;
use Response;

use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\DateTimeTransformerTrait;
use DMA\Friends\API\Transformers\UserMetadataTransformer;

class RewardTransformer extends BaseTransformer {
    
    
    use DateTimeTransformerTrait;
    
    
    public function getData($instance)
    {
        $data = [
            'id'            => $instance->id,
            'title'         => $instance->title,
            'description'   => $instance->description,
            'excerpt'       => $instance->excerpt,
            'is_published'  => ($instance->is_published)?true:false,
            'is_archived'   => ($instance->is_archived)?true:false,
            'is_hidden'     => ($instance->is_hidden)?true:false,
            'points'        => $instance->points,
            'image_url'     => $this->getImageUrl($instance),
            'barcode'       => $instance->barcode,
            'date_begin'    => $this->carbonToIso($instance->date_begin),
            'date_end'      => $this->carbonToIso($instance->date_end),
            'days_valid'    => $instance->days_valid,
            'fine_print'    => $instance->fine_print,
            'inventory'     => $this->getInventory($instance),
            #'enable_email'  => $instance->enable_email,
            #'redemption_email' => $instance->redemption_email,
            #'enable_admin_email' => $instance->enable_admin_email,
            #'email_template' => $instance->email_template,
            #'admin_email_template' => $instance->admin_email_template,
            #'admin_email_group' => $instance->admin_email_group,
            #'admin_email_address' =>  $instance->admin_email_address,
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

    

    /**
     * Normalize inventory value
     * @param Model $instance
     * @return int
     */
    protected function getInventory(Model $instance)
    {
        try{
            if (!is_null($instance->inventory)) {
                return $instance->inventory;
            }
        }catch(\Exception $e){
            // Do nothing
        }
        return 0;
    }
    
}
