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
     * @SWG\Get(
     *     path="activity-logs/user/{user}",
     *     description="Returns all activity logs of a user",
     *     summary="Return activity logs by user",
     *     tags={"activity-logs"},
     *
     *     @SWG\Parameter(
     *         ref="#/parameters/per_page"
     *     ),
     *     @SWG\Parameter(
     *         ref="#/parameters/page"
     *     ),
     *     @SWG\Parameter(
     *         ref="#/parameters/sort"
     *     ),
     *
     *     @SWG\Parameter(
     *         description="ID of user activity logs to fetch",
     *         format="int64",
     *         in="path",
     *         name="user",
     *         required=true,
     *         type="integer"
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/activity.log", type="array")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Not Found",
     *         @SWG\Schema(ref="#/definitions/error404")
     *    )
     * )
     */
    
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
     * @SWG\Parameter(
     *    parameter="activity_log_types",
     *    description="One or Multiple activity log object types",
     *    in="path",
     *    name="types",
     *    type="array",
     *    required=true,
     *    items=@SWG\Schema(type="string"),
     *    collectionFormat="csv",
     *    enum={"reward", "activity", "badge", "step"},
     * )
     *
     *
     * @SWG\Get(
     *     path="activity-logs/{types}",
     *     description="Returns all activity logs by type",
     *     summary="Return activity log by type", 
     *     tags={ "activity-logs"},
     *
     *     @SWG\Parameter(
     *         ref="#/parameters/per_page"
     *     ),
     *     @SWG\Parameter(
     *         ref="#/parameters/page"
     *     ),
     *     @SWG\Parameter(
     *         ref="#/parameters/sort"
     *     ),
     *
     *     @SWG\Parameter(
     *         ref="#/parameters/activity_log_types"
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/activity.log", type="array")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Not Found",
     *         @SWG\Schema(ref="#/definitions/error404")
     *    )
     * )
     *
     * @SWG\Get(
     *     path="activity-logs/{types}/user/{user}",
     *     description="Returns a single activity type log of a given user",
     *     summary="Return users activity log by type",
     *     tags={ "activity-logs"},
     *    
     *     @SWG\Parameter(
     *         ref="#/parameters/per_page"
     *     ),
     *     @SWG\Parameter(
     *         ref="#/parameters/page"
     *     ),
     *     @SWG\Parameter(
     *         ref="#/parameters/sort"
     *     ),
     *     @SWG\Parameter(
     *         ref="#/parameters/activity_log_types"
     *     ),
     *
     *     @SWG\Parameter(
     *         description="ID of user activity medatadata to fetch",
     *         format="int64",
     *         in="path",
     *         name="user",
     *         required=true,
     *         type="integer"
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/activity.log", type="array")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Not Found",
     *         @SWG\Schema(ref="#/definitions/error404")
     *    )
     * )
     */
    
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

    /**
     * @SWG\Get(
     *     path="activity-logs",
     *     description="Returns all activity logs",
     *     summary="Return all activity logs",
     *     tags={ "activity-logs"}, 
     *     
     *     @SWG\Parameter(
     *         ref="#/parameters/per_page"
     *     ),
     *     @SWG\Parameter(
     *         ref="#/parameters/page"
     *     ),
     *     @SWG\Parameter(
     *         ref="#/parameters/sort"
     *     ),
     *          
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/activity.log", type="array")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Not Found",
     *         @SWG\Schema(ref="#/definitions/error404")
     *    )
     * )
     */
    public function index()
    {
        return parent::index();
    }
    
    /**
     * @SWG\Get(
     *     path="activity-logs/{id}",
     *     description="Returns an activity logs by id",
     *     summary="Find an activity logs by id",
     *     tags={ "activity-logs"},
     *      
     *     @SWG\Parameter(
     *         description="ID of activity log to fetch",
     *         format="int64",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/activity.log")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Not Found",
     *         @SWG\Schema(ref="#/definitions/error404")
     *     )
     * )
     */    
    public function show($id)
    {
        return parent::show($id);
    }
    
    
 
}
