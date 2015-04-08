<?php namespace DMA\Friends\API\Resources;

use Log;
use Input;
use Response;
use RainLab\User\Models\User;
use DMA\Friends\Classes\API\BaseResource;

class ActivityLogResource extends BaseResource {

    protected $model        = '\DMA\Friends\Models\ActivityLog';

    protected $transformer  = '\DMA\Friends\API\Transformers\ActivityLogTransformer';


    public function __construct()
    {
        // Add additional routes to Activity resource
        $this->addAdditionalRoute('indexByUser',        'user/{user}',          ['GET']);
        $this->addAdditionalRoute('indexByTypeAndUser', '{types}',              ['GET']);
        $this->addAdditionalRoute('indexByTypeAndUser', '{types}/user/{user}',  ['GET']);

    }
    
    /**
     * Provide aliases to user_id filters
     * @param string $user
     * @return Response
     */
    public function indexByUser($user=null)
    {
        return $this->indexByTypeAndUser(null, $user);
    }
    
    /**
     * Provide aliases to object_type and user_id filters
     * @param string $types comma separted strings
     * @param string $user
     * @return Response
     */
    public function indexByTypeAndUser($types=null, $user=null)
    {
        
        $filters = [];
        if (!is_null($types)) {
            
            $relModel = [
                    'reward'   => 'DMA\Friends\Models\Reward',
                    'activity' => 'DMA\Friends\Models\Activity',
                    'badge'    => 'DMA\Friends\Models\Badge',
                    'step'     => 'DMA\Friends\Models\Step',
            ];
            
            $types = !is_null($types) ? explode(',', $types): $types;

            try{
                
                $object_types = array_map(function($t) use ($relModel) {
                    $t = strtolower(trim($t));
                    return $relModel[$t];
                }, $types);
                
            } catch (\Exception $e) {
                Log::error('API endpoint ' . get_class($this) . ' : ' . $e->getMessage());
                $message = 'One of the given types is not valid. Valid types are [ ' . implode(', ', array_keys($relModel)) .' ]';
                return Response::api()->errorInternalError($message);
            }
            
            if( count($object_types) > 0 ) {
                $filters['object_type__in'] = $object_types;
            }
        }
        

        if (!is_null($user)) {
            // Find user if exists
            $user = User::find($user);
            if ($user) {
                $filters['user_id'] = $user->getKey();
            } else {
                return Response::api()->errorNotFound('User not found');
            }
        }
        
        
        // Update or inject Input filters
        Input::merge($filters);

        // Call index and the filters will do the magic
        return parent::index();
    }


    
 
}
