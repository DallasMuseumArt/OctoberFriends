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
        $this->addAdditionalRoute('removeObjectBookmark', '',        ['DELETE']);
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
            'activity' => '\DMA\Friends\Models\Activity',        
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
     *      definition="request.bookmark",
     *      required={"object_type", "object_id", "user_id"},
     *      @SWG\Property(
     *          property="object_type",
     *          type="string",
     *          enum={"activity","badge", "reward"}
     *      ),
     *      @SWG\Property(
     *          property="object_id",
     *          type="integer",
     *          format="int32"
     *      ),
     *      @SWG\Property(
     *          property="user_id",
     *          type="integer",
     *          format="int32"
     *      )  
     * ) 
     * 
     * @SWG\Definition(
     *      definition="response.bookmark",
     *      required={"data"},
     *      @SWG\Property(
     *          property="data",
     *          type="object",
     *          ref="#/definitions/bookmark.payload"
     *      )
     * )
     * 
     * @SWG\Definition(
     *      definition="bookmark.payload",
     *      required={"success", "message"},
     *      @SWG\Property(
     *          property="success",
     *          type="boolean"
     *      ),
     *      @SWG\Property(
     *          property="message",
     *          type="string"
     *      ) 
     * )
     * 
     * 
     * @SWG\POST(
     *     path="bookmark/",
     *     description="Add Object to User's  bookmark list",
     *     summary="Create an bookmark",
     *     tags={ "bookmarks"},
     *
     *     @SWG\Parameter(
     *         ref="#/parameters/authorization"
     *     ),
     *     @SWG\Parameter(
     *         in="body",
     *         name="body",
     *         required=true,
     *         type="object",
     *         schema=@SWG\Schema(ref="#/definitions/request.bookmark")
     *     ), 
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/response.bookmark")
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
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\API\BaseResource::store()
     */
    public function store()
    {
        return $this->prepareBookmardata('add');
    }
    
     /**
     * 
     * 
     * @SWG\DELETE(
     *     path="bookmark/",
     *     description="Remove Object from User's bookmark list",
     *     summary="Remove an bookmark",
     *     tags={ "bookmarks"},
     *
     *     @SWG\Parameter(
     *         ref="#/parameters/authorization"
     *     ),
     *     @SWG\Parameter(
     *         in="body",
     *         name="body",
     *         required=true,
     *         type="object",
     *         schema=@SWG\Schema(ref="#/definitions/request.bookmark")
     *     ), 
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/response.bookmark")
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
     * Remove Bookmark
     */
    public function removeObjectBookmark()
    {
        return $this->prepareBookmardata('remove');
    }
     
    
    protected function prepareBookmardata($action)
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
    
    /**
     * Add or Remove bookmarks in the platform
     * 
     * @param sting $action Options are 'add' and 'remove'
     * @param string $objectType Options are 'badge', 'activity', 'reward'
     * @param integer $objectId Id of the bookmarked objetct 
     * @param integer $user Id of the user bookmaring the object
     */
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
                        $httpCode = 409;
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
                return Response::api()->errorNotFound("$objectType not found");
            }

        }else{
            return Response::api()->errorNotFound('User not found');
        }

         
        
    }
    
 
   /**
    * {@inheritDoc}
    * @see \DMA\Friends\Classes\API\BaseResource::index()
    */
    public function index()
    {
        return Response::api()->errorForbidden();
    }
   
    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\API\BaseResource::show()
     */
    public function show($id)
    {
        return Response::api()->errorForbidden();
    }
    
    
}