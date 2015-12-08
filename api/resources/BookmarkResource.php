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
        //$this->addAdditionalRoute('removeObjectBookmark',         'remove/{object}/{objectId}/user/{user}',  ['GET']);
        //$this->addAdditionalRoute('addObjectBookmark',            'add/{object}/{objectId}/user/{user}',     ['GET']);        
        //$this->addAdditionalRoute('removeObjectBookmarkByDelete', 'remove',  ['POST']);
        //$this->addAdditionalRoute('addObjectBookmarkByPost',      'add',     ['POST']);
        $this->addAdditionalRoute('removeObjectBookmarkByDelete', '',        ['DELETE']);
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

    /**
     * (non-PHPdoc)
     * @see \DMA\Friends\Classes\API\BaseResource::store()
     */
    public function store()
    {
        return $this->addObjectBookmarkByPost();
    }
    
    public function addObjectBookmark($objectType, $objectId, $user)
    {
        return $this->doObjectBookmark('add', $objectType, $objectId, $user);
    }

    public function addObjectBookmarkByPost()
    {
        return $this->doObjectBookmarkByPost('add');
    }
    
    public function removeObjectBookmark($objectType, $objectId, $user)
    {
        return $this->doObjectBookmark('remove', $objectType, $objectId, $user);
    }
    
    
    public function removeObjectBookmarkByDelete()
    {
        return $this->doObjectBookmarkByPost('remove');
    }
     
    
    protected function doObjectBookmarkByPost($action)
    {
        $data = Request::all();
        $rules = [
                'object_type'             => "required",
                'object_id'               => "required",
                'user_id'                 => "required"
        ];
        
        $validation = Validator::make($data, $rules);
        if ($validation->fails()){
            return $this->errorDataValidation('Bookmark data fails to validated', $validation->errors());
        }
         
        
        return $this->doObjectBookmark($action, $data['object_type'], $data['object_id'], $data['user_id']);
    }
    
    
    protected function doObjectBookmark($action, $objectType, $objectId, $user)
    {

        if($user = User::find($user)){

            if($instance = $this->getObject($objectType, $objectId)){
                $success = true;
                $httpCode = 200;
                
                if ($action == 'add'){
                    $bookmark = Bookmark::saveBookmark($user, $instance);
                    if ($bookmark->isNew){
                        $httpCode = 201;
                        $message = "$objectType has been bookmark succesfully.";
                    }else{
                        $httpCode = 200;
                        $success = false;
                        $message = "The user has already bookmark this '$objectType'.";
                    }
                }else if($action == 'remove'){
                    $success = Bookmark::removeBookmark($user, $instance);
                    if($success){
                        //$httpCode = 204;
                        $httpCode = 200;
                        $message = 'Bookmark has been removed successfully.';
                        
                    }else{
                        $httpCode = 404;
                        $message = "User don't have the given Bookmark.";
                    }

                }

                $payload = [
                        'data' => [
                                'success' => $success,
                                'message' => $message,
                         ]
                ];
                 
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