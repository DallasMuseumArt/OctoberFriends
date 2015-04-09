<?php namespace DMA\Friends\API\Transformers;

use Model;
use DMA\Friends\Classes\API\BaseTransformer;
use DMA\Friends\API\Transformers\DateTimeTransformerTrait;

class ActivityLogTransformer extends BaseTransformer {
    
    use DateTimeTransformerTrait;
    
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $defaultIncludes = [
            'user',
            'object'
    ];
    
    public function getData($instance)
    {
        return [
            'id'    => (int)$instance->id,
            'action' => $instance->action,
            'message' => $instance->message,
            'points_earned' => $instance->points_earned,
            'total_points' => $instance->total_points,   
            //'object_type'  => $instance->object_type,  
            'timestamp' => $this->carbonToIso($instance->timestamp),    
        ];
    }

    /**
     * Include Steps
     *
     * @return League\Fractal\ItemResource
     */
    public function includeObject(Model $instance)
    {
   
        $class  = $instance->object_type;
        // There are logs without object
        if ($class) {
            $obj_id = $instance->object_id;
            $relObj = $instance->object;
            
            
            $transformer = null;
            
            switch ($class) {
                case 'DMA\Friends\Models\Reward':
                    $transformer = '\DMA\Friends\API\Transformers\RewardTransformer';
                    break;
                case 'DMA\Friends\Models\Activity':
                    $transformer = '\DMA\Friends\API\Transformers\ActivityTransformer';
                    break;
                case 'DMA\Friends\Models\Step':
                    $transformer = '\DMA\Friends\API\Transformers\StepTransformer';
                    break;
                case 'DMA\Friends\Models\Badge':
                    $transformer = '\DMA\Friends\API\Transformers\BadgeTransformer';
                    break;
                default:
                    $transformer = 'DMA\Friends\Classes\API\BaseTransformer';
                    break;
            }
           
            return $this->item($relObj, new $transformer);
        }
        return null;
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
