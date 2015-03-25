<?php namespace DMA\Friends\API\Transformers;

use Model;
use Response;

use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\ActivityTransformer;
use DMA\Friends\API\Transformers\BadgeTransformer;
use DMA\Friends\API\Transformers\RewardTransformer;
use DMA\Friends\API\Transformers\UserProfileTransformer;


class UserTransformer extends BaseTransformer {
    
    /*
     * @var boolean transform profile user data
     */
     private $include_profile = false;
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
            'profile',
            'rewards',
            'activities',
            'badges'
    ];
    
    
    public function __construct($include_profile=false)
    {
        $this->include_profile = $include_profile;
    }
    
    public function getData($instance)
    {
        $data = [
            'id' => (int)$instance->id, 
            // first_name and last_name are stored in usermetadata model. 
            // not sure why were are not using name and surname of the User model     
            'first_name' => $this->getMetadataValue($instance, 'first_name'),
            'last_name' => $this->getMetadataValue($instance, 'last_name'),
            'username' => $instance->username,
            'avatar_url' => $this->getAvatar($instance),
        ];
        
        if ($this->include_profile) {
            $this->setDefaultIncludes(['profile', 'rewards', 'activities', 'badges']);
        }
        
        return $data;
    }

    /**
     * Get URL path to user avatar
     * @param unknown $instance
     */
    protected function getAvatar(Model $instance)
    {
        try{
            if (!is_null($instance->avatar)) {
                return $instance->avatar->getThumb(50, 50);
            }
        }catch(\Exception $e){
            // Do nothing
        }
        return null;
    }
    
    /**
     * Helper method to get User metadata fields 
     * @param Model $instance
     * @param string $fieldname
     * @return mixed
     */
    protected function getMetadataValue(Model $instance, $fieldname)
    {
        if (!is_null($instance->metadata)) {
            return $instance->metadata->{$fieldname};
        }
        return null;
    }
    

    /**
     * Include Users profile data from other models
     *
     * @return League\Fractal\ItemResource
     */
    public function includeProfile(Model $instance)
    {
        return $this->item($instance, new UserProfileTransformer);
    }
    
    /**
     * Include User rewards
     *
     * @return League\Fractal\CollectionResource
     */
    public function includeRewards(Model $instance)
    {
    
        $rewards = $instance->rewards;
        $item = $this->collection($rewards, new RewardTransformer);
        return $item;
    
    }

    /**
     * Include User activites
     *
     * @return League\Fractal\CollectionResource
     */
    public function includeActivities(Model $instance)
    {
        $rewards = $instance->rewards;
        return $this->collection($rewards, new ActivityTransformer);    
    }
    
    
    /**
     * Include User badges
     *
     * @return League\Fractal\CollectionResource
     */
    public function includeBadges(Model $instance)
    {
        $badges = $instance->badges;
        return $this->collection($badges, new BadgeTransformer);
    }
}
