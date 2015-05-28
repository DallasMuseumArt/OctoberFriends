<?php namespace DMA\Friends\API\Transformers;

use Model;
use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\CountryTransformer;
use DMA\Friends\API\Transformers\StateTransformer;
use DMA\Friends\API\Transformers\UserMetadataTransformer;

class UserProfileTransformer extends BaseTransformer {
    
    
    /**
     * List of default resources to include
     *
     * @var array
     */
    protected $defaultIncludes = [
            'country',
            'state',
    ];

    public function getData($instance)
    {
        $data = [
            'barcode_id'         => $instance->barcode_id,
            'is_activated'       => $instance->is_activated,
            'email'              => $instance->email,
            'phone'              => $instance->phone,
            'address'            => $instance->street_addr,  
            'city'               => $instance->city,
            'zip'                => $instance->zip,
            'points'             => $this->getUserPoints($instance)
        ];
        
        // Add to profile user metadata
        $data = array_merge($data, $this->getUserMetadata($instance));
                
        return $data;
    }    
    
    
   
    /**
     * Include Country
     *
     * @return League\Fractal\ItemResource
     */
    public function includeCountry(Model $instance)
    {
    
        if (is_null($country = $instance->country)){
            // Check if state is been set
            if(!is_null($state = $instance->state)){
                $country = $state->country;
            }
        }
        $item = $this->item($country, new CountryTransformer);
        return $item;
        
    }
    
    
    /**
     * Include State
     *
     * @return League\Fractal\ItemResource
     */
    public function includeState(Model $instance)
    {
    
        $state = $instance->state;
        return $this->item($state, new StateTransformer);
    }

    
    private function getUserMetadata(Model $instance)
    {
        $metadata = $instance->metadata;
        if(!is_null($metadata)){
            $item = new UserMetadataTransformer;
            return $item->getData($metadata);
        }
        
        return [];
        
    }
    
    public function getUserPoints(Model $instance)
    {
        return [
            'total'      => intval($instance->points),
            'this_week'  => intval($instance->points_this_week),
            'today'      => intval($instance->points_today)
        ];
    }
    
    
}
