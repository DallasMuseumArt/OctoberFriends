<?php namespace DMA\Friends\API\Resources;

use Session;
use Response;
use RainLab\User\Models\User;

use DMA\Friends\Classes\API\BaseResource;
use DMA\Friends\Classes\API\ModelRepository;

use DMA\Friends\Activities\ActivityCode;
use DMA\Friends\Activities\LikeWorkOfArt;
use DMA\Friends\Models\ActivityMetadata;

class ActivityMetadataResource extends BaseResource {

    protected $model        = '\DMA\Friends\Models\ActivityMetadata';

    protected $transformer  = '\DMA\Friends\API\Transformers\ActivityMetadataTransformer';


    public function __construct()
    {
        // Add additional routes to Activity resource
        $this->addAdditionalRoute('indexByUser',        'user/{user}',          ['GET']);
        $this->addAdditionalRoute('indexByTypeAndUser', '{types}',              ['GET']);
        $this->addAdditionalRoute('indexByTypeAndUser', '{types}/user/{user}',  ['GET']);        
        
    }
    
    /**
     * @SWG\Get(
     *     path="activity-metadata/user/{user}",
     *     description="Returns all activity medatadata of a user",
     *     summary="Returns all activity medatadata by user id",
     *     tags={ "activity-metadata"},
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
     *         @SWG\Schema(ref="#/definitions/activity.metadata", type="array")
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
     * Return activity metadata by the given user
     * @param string $userId
     * @return Response
     */
    public function indexByUser($userId=null)
    {
        return $this->indexByTypeAndUser(null, $userId);
    }

    
    /**
     * @SWG\Parameter(
     *    parameter="activity_metadata_types",
     *    description="One or Multiple activity types",
     *    in="path",
     *    name="types",
     *    type="array",
     *    required=true,
     *    items=@SWG\Schema(type="string"),
     *    collectionFormat="csv",
     *    enum={"ActivityCode", "LikeWorkOfArt", "Points", "Registration"},
     * )
     *
     * 
     * @SWG\Get(
     *     path="activity-metadata/{types}",
     *     description="Returns all activity medatadata of activity type",
     *     summary="Return activity metadata by activity type",
     *     tags={ "activity-metadata"},
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
     *
     *     @SWG\Parameter(
     *         ref="#/parameters/activity_metadata_types"    
     *     ),
     *     
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/activity.metadata", type="array")
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
     *     path="activity-metadata/{types}/user/{user}",
     *     description="Returns all activity medatadata of a user",
     *     summary="Return activity metadata by activity type and user id",
     *     tags={ "activity-metadata"},
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
     *         ref="#/parameters/activity_metadata_types"     
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
     *         @SWG\Schema(ref="#/definitions/activity.metadata", type="array")
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
     * Return activity metadata by the given user filtered
     * by activities types
     * @param string $types comma separted strings
     * @param string $user
     * @return Response
     */
    public function indexByTypeAndUser($types=null, $userId=null)
    {
        // Apply query filters and sort parameters
        $query = $this->applyFilters();
        
        if(!is_null($userId)){
            if(is_null($user = User::find($userId))){
                return Response::api()->errorNotFound('User not found');
            }
            
            // Filter query by user
            $query = $query->where('user_id', $user->getKey());
        }

    
        // Filter by activity type
        $types = !is_null($types) ? explode(',', $types): $types;
        if(!is_null($types)) {
            $query = $query->whereHas('activity', function($q) use ($types) {
                $q->whereIn('activity_type', $types);
            });
        }
        
        return $this->paginateResult($query);

    }
    
    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\API\BaseResource::applyFilters()
     */
    protected function applyFilters(ModelRepository $model = null)
    {
        $query = parent::applyFilters($model);
        // TODO : is better to transpode the data rows to columns
        // but for now the getMetadata method on ActivityMetadataTransformer
        // and the following groupBy would work.
         
        // Is necessary group by session_id, otherwise 
        // the response will include duplicates. 
        $query = $query->groupBy('session_id');

        return $query;
    }
    
    
    /**
     * @SWG\Get(
     *     path="activity-metadata",
     *     description="Returns all activity medatadata",
     *     summary="Return all activity metadata",
     *     tags={ "activity-metadata"},
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
     *         @SWG\Schema(ref="#/definitions/activity.metadata", type="array")
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
     *     path="activity-metadata/{id}",
     *     description="Returns an activity metadata by id",
     *     summary="Find an activity metadata by id",
     *     tags={ "activity-metadata"},
     *      
     *     @SWG\Parameter(
     *         description="ID of activity metadata to fetch",
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
     *         @SWG\Schema(ref="#/definitions/activity.metadata")
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
