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

    /**
     * @SWG\Definition(
     *    definition="user.profile",
     *    description="User profile definition",
     *    required={"barcode_id", "is_activated", "phone", "address", "city", "zip", "points", "country", "state"},
     *    @SWG\Property(
     *         property="barcode_id",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="is_activated",
     *         type="boolean",
     *    ),
     *    @SWG\Property(
     *         property="phone",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="address",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="city",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="zip",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         description="Classname and Namespace of the generic polymorphic relationship",
     *         property="object_type",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="timestamp",
     *         type="string",
     *         format="date-time"
     *    ),
     *    @SWG\Property(
     *         property="points",
     *         type="object",
     *         ref="#/definitions/user.points"
     *    ),
     *    @SWG\Property(
     *         property="country",
     *         type="object",
     *         ref="#/definitions/country"
     *    ),
     *    @SWG\Property(
     *         property="state",
     *         type="object",
     *         ref="#/definitions/state"
     *    ),
     * )
     */
    
    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\API\BaseTransformer::getData()
     */
    
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
    
    /**
     * @SWG\Definition(
     *     definition="user.points",
     *     type="object",
     *     required={"total", "this_week", "today"},
     *     @SWG\Property(
     *         property="total",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @SWG\Property(
     *         property="this_week",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @SWG\Property(
     *         property="today",
     *         type="integer",
     *         format="int32"
     *     )
     * )
     */
    
    public function getUserPoints(Model $instance)
    {
        return [
            'total'      => intval($instance->points),
            'this_week'  => intval($instance->points_this_week),
            'today'      => intval($instance->points_today)
        ];
    }
    
    
}
