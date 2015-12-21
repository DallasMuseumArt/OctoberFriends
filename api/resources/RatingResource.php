<?php namespace DMA\Friends\API\Resources;

use Response;
use Request;
use Validator;
use DMA\Friends\Models\Rating;
use DMA\Friends\Models\Activity;
use DMA\Friends\Classes\API\BaseResource;
use DMA\Friends\API\Transformers\UserProfileTransformer;
use RainLab\User\Models\User;

class RatingResource extends BaseResource {

    protected $model        = '\DMA\Friends\Models\Rating';
    protected $transformer  = '\DMA\Friends\API\Transformers\RatingTransformer';
    
    /**
     * The listed actions that don't required check if
     * user can perform the action
     * @var array
     */
    protected $skipUserPermissionValidation = [
            'index', 'show'
    ];
    
    
    public function __construct()
    {
        // Add additional routes to Activity resource
        $this->addAdditionalRoute('ratingsByObject',     '{object}/{objectId}',                             ['GET']);
        $this->addAdditionalRoute('addObjectRateJson',   'rate/{object}/',                                  ['POST']);
        $this->addAdditionalRoute('addObjectRating',     'rate/{object}/{objectId}/user/{user}/{rate}',     ['GET']);

    }
    
    
    /**
     * Get instance of the object
     * @param string $objectType
     * @param string $objectId
     * @return mixed
     */
    protected function getObject($objectType, $objectId)
    {
        $registry = [
            'activity' => '\DMA\Friends\Models\Activity',        
            'badge'    => '\DMA\Friends\Models\Badge',
        ];
        
        $model = array_get($registry, $objectType);
        if ($model){
            return call_user_func_array("{$model}::find", [$objectId]);
        }else{
            $options = implode(', ', array_keys($registry));
            throw new \Exception("$objectType is not register as rateable. Options are $options");
        }
    }
    
    
    
    /**
     * @SWG\GET(
     *     path="ratings/{object}/{objectId}",
     *     description="Get all object ratings",
     *     summary="Get all ratings by object type",
     *     tags={ "ratings" },
     *     
     *     @SWG\Parameter(
     *         description="Object",
     *         in="path",
     *         name="object",
     *         required=true,
     *         type="string",
     *         enum={"activity", "badge"}
     *     ),
     *      
     *     @SWG\Parameter(
     *         ref="#/parameters/authorization"
     *     ),
     *     @SWG\Parameter(
     *         ref="#/parameters/per_page"
     *     ),
     *     @SWG\Parameter(
     *         ref="#/parameters/page"
     *     ),
     *     
     *     @SWG\Parameter(
     *         description="ID of object to fetch",
     *         format="int64",
     *         in="path",
     *         name="objectId",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/rate")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="User not found",
     *         @SWG\Schema(ref="#/definitions/UserError404")
     *     )
     * )
     */
    
    /**
     * Get ratings by object
     * @param string $objectType
     * @param int $objectId
     */
    public function ratingsByObject($objectType, $objectId)
    {
         if($instance = $this->getObject($objectType, $objectId)){
             
             $pageSize  = $this->getPageSize();
             $paginator = $instance->getRates()->paginate($pageSize);
             $meta      = [
                     'rating' => array_merge(
                             $instance->getRatingStats(),
                             [
                                     'object_type' => $objectType,
                                     'object_id'   => intval($objectId)
                             ]
                     )
             ];
             
             $transformer = new \DMA\Friends\API\Transformers\RateTransformer;
             return Response::api()->withPaginator($paginator, $transformer, null, $meta);
             
         } else {
            return Response::api()->errorNotFound("$objectType not found");
         }
    }
    
    

    /**
     * @SWG\Definition(
     *      definition="response.rate",
     *      required={"data"},
     *      @SWG\Property(
     *          property="data",
     *          type="object",
     *          ref="#/definitions/rate.payload"
     *      )
     * )
     * 
     * @SWG\Definition(
     *      definition="rate.payload",
     *      required={"success", "message", "user", "rating"},
     *      @SWG\Property(
     *          property="success",
     *          type="boolean"
     *      ),
     *      @SWG\Property(
     *          property="message",
     *          type="string"
     *      ),
     *      @SWG\Property(
     *          property="user",
     *          type="object",
     *          ref="#/definitions/user.info.points"
     *      ),
     *      @SWG\Property(
     *          property="rating",
     *          type="object",
     *          ref="#/definitions/rating.stats"
     *      )   
     * )
     * 
     * @SWG\Definition(
     *      definition="rating.stats",
     *      required={"total", "average", "object_type", "object_id"},
     *      @SWG\Property(
     *          property="total",
     *          type="number",
     *          format="float"
     *      ),
     *      @SWG\Property(
     *          property="average",
     *          type="number",
     *          format="float"
     *      ),
     *      @SWG\Property(
     *          property="object_type",
     *          type="string"
     *      ),
     *      @SWG\Property(
     *          property="object_id",
     *          type="integer",
     *          format="int32"
     *      )  
     * )
     * 
     * 
     * @SWG\GET(
     *     path="ratings/rate/{object}/{objectId}/user/{user}/{rate}",
     *     description="Rate an object",
     *     summary="Rate an object",
     *     tags={ "ratings"},
     *
     *     @SWG\Parameter(
     *         ref="#/parameters/authorization"
     *     ),
     *     @SWG\Parameter(
     *         description="Object to rate",
     *         in="path",
     *         name="object",
     *         required=true,
     *         type="string",
     *         enum={"activity", "badge"}
     *     ),
     *     @SWG\Parameter(
     *         description="ID of object to rate",
     *         format="int64",
     *         in="path",
     *         name="objectId",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         description="ID of user",
     *         format="int64",
     *         in="path",
     *         name="user",
     *         required=true,
     *         type="integer"
     *     ),     
     *     @SWG\Parameter(
     *         description="Rate value",
     *         format="float",
     *         in="path",
     *         name="rate",
     *         required=true,
     *         type="number",
     *         minimum=1,
     *         maximum=5
     *     ),  
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/response.rate")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="User not found",
     *         @SWG\Schema(ref="#/definitions/UserError404")
     *     )
     * )
     */

    public function addObjectRating($objectType, $objectId, $user, $rateValue, $comment = null)
    {

        if($user = User::find($user)){

            if($instance = $this->getObject($objectType, $objectId)){

                list($success, $rating) = $instance->addRating($user, $rateValue, $comment);
                
                // Get common user points format via UserProfileTransformer
                $userTransformer = new UserProfileTransformer();
                $points = $userTransformer->getUserPoints($user);

                $payload = [
                        'data' => [
                                'success' => $success,
                                'message' => "$objectType has been rate succesfully.",
                                'user' => [
                                        'id'      => $user->getKey(),
                                        'points'  => $points
                                ],
                                'rating' => array_merge(
                                    $instance->getRatingStats(),
                                    [
                                        'object_type' => $objectType,
                                        'object_id'   => intVal($objectId)
                                    ]
                                 )
                        ]
                ];
                
                
                $httpCode = 201;
                
                if( !$success ) {
                    $httpCode = 200;
                    $payload['data']['message'] = "User has already rate this $objectType";
                }
                
                return Response::api()->setStatusCode($httpCode)->withArray($payload);

            } else {
                return Response::api()->errorNotFound("$object not found");
            }

        }else{
            return Response::api()->errorNotFound('User not found');
        }

         
        
    }
    
    /**
     * @SWG\Definition(
     *      definition="request.rate",
     *      required={"id", "rate", "user_id"},
     *      @SWG\Property(
     *          property="id",
     *          type="integer",
     *          format="int32"
     *      ),
     *      @SWG\Property(
     *          property="rate",
     *          type="number",
     *          format="float"
     *      ),
     *      @SWG\Property(
     *          property="user_id",
     *          type="integer",
     *          format="int32"
     *      )  
     * )
     * 
     * 
     * @SWG\Post(
     *     path="ratings/rate/{object}/",
     *     description="Rate an object",
     *     summary="Rate an object",    
     *     tags={ "ratings"},
     *     
     *     @SWG\Parameter(
     *         ref="#/parameters/authorization"
     *     ),
     *     @SWG\Parameter(
     *         description="Object to rate",
     *         in="path",
     *         name="object",
     *         required=true,
     *         type="string",
     *         enum={"activity", "badge"}
     *     ),
     *     @SWG\Parameter(
     *         in="body",
     *         name="body",
     *         required=true,
     *         type="object",
     *         schema=@SWG\Schema(ref="#/definitions/request.rate")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/response.rate")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="User not found",
     *         @SWG\Schema(ref="#/definitions/UserError404")
     *     )
     * )
     */
    public function addObjectRateJson($objectType){
        $data = Request::all();
        $rules = [
                'id'                    => "required",
                'rate'                  => "required",
                'user_id'               => "required"
        ];
        
        $validation = Validator::make($data, $rules);
        if ($validation->fails()){
            return $this->errorDataValidation('rate data fails to validated', $validation->errors());
        }
        
        $comment = array_get($data, 'comment', '');
        return $this->addObjectRating($objectType, $data['id'], $data['user_id'], $data['rate'], $comment);
        
    }
   
    public function index()
    {
        # TODO : stop default behaviour of the base resource and 
        # return and error
        //return Response::api()->errorForbidden();
        return parent::index();
    }
    
    /**
     * @SWG\Get(
     *     path="ratings/{id}",
     *     description="Returns an rate by id",
     *     summary="Find a rate by id",
     *     tags={ "ratings"},
     *
     *     @SWG\Parameter(
     *         ref="#/parameters/authorization"
     *     ),
     *     @SWG\Parameter(
     *         description="ID of rating to fetch",
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
     *         @SWG\Schema(ref="#/definitions/rate")
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