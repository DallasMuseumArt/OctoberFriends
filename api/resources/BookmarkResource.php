<?php namespace DMA\Friends\API\Resources;

use Response;
use Request;
use Validator;
use DMA\Friends\Models\Bookmark;
use DMA\Friends\Classes\API\BaseResource;
use RainLab\User\Models\User;

class BookmarkResource extends BaseResource {

    protected $model        = '\DMA\Friends\Models\Bookmark';
    protected $transformer  = '\DMA\Friends\API\Transformers\BookmarkTransformer';
    
   
    
    
    public function __construct()
    {
        // Add additional routes to Bookmark resource
        $this->addAdditionalRoute('removeObjectBookmark',   'remove/{object}/{objectId}/user/{user}',  ['GET']);
        $this->addAdditionalRoute('addObjectBookmark',      'add/{object}/{objectId}/user/{user}',     ['GET']);

    }
    
    
    /**
     * Get instance of the object
     * @param string $objectType
     * @param string $objectId
     * @return mixed
     */
    protected function getObject($objectType, $objectId)
    {
        # TODO : Allow to bookmark Activities 
        $registry = [
            # 'activity' => '\DMA\Friends\Models\Activity',        
            'badge'    => '\DMA\Friends\Models\Badge',
            'reward'   => '\DMA\Friends\Models\Reward',
        ];
        
        $model = array_get($registry, $objectType);
        if ($model){
            return call_user_func_array("{$model}::find", [$objectId]);
        }else{
            $options = implode(', ', array_keys($registry));
            throw new \Exception("$objectType is not a valid option. Options are $options");
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

    public function addObjectBookmark($objectType, $objectId, $user)
    {
        return $this->doObjectBookmark('add', $objectType, $objectId, $user);
    }

    
    public function removeObjectBookmark($objectType, $objectId, $user)
    {
        return $this->doObjectBookmark('remove', $objectType, $objectId, $user);
    }
    
    
    protected function doObjectBookmark($action, $objectType, $objectId, $user)
    {

        if($user = User::find($user)){

            if($instance = $this->getObject($objectType, $objectId)){
                $success = true;
                
                if ($action == 'add'){
                    Bookmark::saveBookmark($user, $instance);
                    $message = "$objectType has been bookmark succesfully.";
                }else if($action == 'remove'){
                    Bookmark::removeBookmark($user, $instance);
                    $message = "Bookmark has been remove succesfully.";
                }

                $payload = [
                        'data' => [
                                'success' => $success,
                                'message' => $message,
                         ]
                ];
                
                
                $httpCode = 201;
                
                if( !$success ) {
                    $httpCode = 200;
                    $payload['data']['message'] = "User has already bookmark this $objectType";
                }
                
                return Response::api()->setStatusCode($httpCode)->withArray($payload);

            } else {
                return Response::api()->errorNotFound("$object not found");
            }

        }else{
            return Response::api()->errorNotFound('User not found');
        }

         
        
    }
    
 
   
    public function index()
    {
        # TODO : stop default behaviour of the base resource and 
        # return and error
        return Response::api()->errorForbidden();
        #return parent::index();
    }
    
    /**
     * @SWG\Get(
     *     path="ratings/{id}",
     *     description="Returns an rate by id",
     *     summary="Find a rate by id",
     *     tags={ "ratings"},
     *
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
        return Response::api()->errorForbidden();
    }
    
    
}