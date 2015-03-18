<?php namespace DMA\Friends\API\Transformers;

use Model;

use DMA\Friends\Classes\API\BaseTransformer;

class ActivityLogTransformer extends BaseTransformer {
    
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $defaultIncludes = [
            'user'
    ];
    
    public function getDatas($instance)
    {
        return [
            'id'    => (int)$instance->id,
            'title' => $instance->title,
        ];
    }

    /**
     * Include Steps
     *
     * @return League\Fractal\ItemResource
     */
    public function includeUser(Model $instance)
    {
 
        $user = $instance->user;
        return $this->item($user, new UserTransformer);
    }


    
}
