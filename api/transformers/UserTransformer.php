<?php namespace DMA\Friends\API\Transformers;

use URL;
use Model;
use Response;

use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\ActivityTransformer;
use DMA\Friends\API\Transformers\BadgeTransformer;
use DMA\Friends\API\Transformers\RewardTransformer;
use DMA\Friends\API\Transformers\UserProfileTransformer;


class UserTransformer extends BaseTransformer {
    

    /**
     * By default always show extended data
     * @var boolean
     */
    protected $useExtendedData = false;
     
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
            'profile'
    ];
    
    /**
     * @SWG\Definition(
     *    definition="user",
     *    description="User profile definition",
     *    required={"id", "first_name", "last_name", "username", "avatar_url"},
     *    @SWG\Property(
     *         property="id",
     *         type="integer",
     *         format="int32"
     *    ),
     *    @SWG\Property(
     *         property="first_name",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="last_name",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="username",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="avatar_url",
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
            'id' => (int)$instance->id, 
            // first_name and last_name are stored in usermetadata model. 
            // not sure why were are not using name and surname of the User model     
            'first_name' => $this->getMetadataValue($instance, 'first_name'),
            'last_name' => $this->getMetadataValue($instance, 'last_name'),
            'username' => $instance->username,
            'avatar_url' => $this->getAvatar($instance),
        ];
        
        return $data;
    }

    /**
     * @SWG\Definition(
     *    definition="user.extended",
     *    description="User extended definition",
     *    required={"id", "first_name", "last_name", "username", "avatar_url", "profile"},
     *    @SWG\Property(
     *         property="id",
     *         type="integer",
     *         format="int32"
     *    ),
     *    @SWG\Property(
     *         property="first_name",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="last_name",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="username",
     *         type="string",
     *    ),
     *    @SWG\Property(
     *         property="avatar_url",
     *         type="integer",
     *         format="int32"
     *    ),
     *    @SWG\Property(
     *         property="num_rewards",
     *         type="integer",
     *         format="int32"
     *    ),
     *    @SWG\Property(
     *         property="num_activities",
     *         type="integer",
     *         format="int32"
     *    ),
     *    @SWG\Property(
     *         property="num_badges",
     *         type="integer",
     *         format="int32"
     *    ),
     *    @SWG\Property(
     *         property="profile",
     *         type="object",
     *         ref="#/definitions/user.profile"
     *    )
     * )
     */
    

    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\API\BaseTransformer::getExtendedData()
     */
    public function getExtendedData($instance)
    {
        // Adding full user profile by the Fractal embeding system
        $this->setDefaultIncludes(['profile']);
        
        return [

                "num_rewards"    => $instance->rewards()->count(),            
                "num_activities" => $instance->activities()->count(),
                "num_badges"     => $instance->badges()->count(),
                "num_bookmarks"  => $instance->bookmarks()->count(),
                
                "rewards_url"    => URL::route('friends.api.users.userrewards',    ['user' => $instance->getKey()]),
                "activities_url" => URL::route('friends.api.users.useractivities', ['user' => $instance->getKey()]),
                "badges_url"     => URL::route('friends.api.users.userbadges',     ['user' => $instance->getKey()]),
                "bookmarks_url"  => URL::route('friends.api.users.userbookmarks',  ['user' => $instance->getKey()])
        ];
        
    }
    
    /**
     * Get URL path to user avatar
     * @param unknown $instance
     */
    protected function getAvatar(Model $instance)
    {
        try{
            if (!is_null($instance->avatar)) {
                return $instance->avatar->getThumb('auto', 'auto', ['extension' => 'png']);
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
        $item = $this->collection($rewards, new RewardTransformer(false));
        return $item;
    
    }

    /**
     * Include User activites
     *
     * @return League\Fractal\CollectionResource
     */
    public function includeActivities(Model $instance)
    {
        $activities = $instance->activities;
        $excludeEmbeds = [];//['categories'];
        return $this->collection($activities, new ActivityTransformer(false, $excludeEmbeds));    
    }
    
    
    /**
     * Include User badges
     *
     * @return League\Fractal\CollectionResource
     */
    public function includeBadges(Model $instance)
    {
        $badges = $instance->badges;
        $excludeEmbeds = [];//['categories'];
        return $this->collection($badges, new BadgeTransformer(false, $excludeEmbeds));
    }
}
