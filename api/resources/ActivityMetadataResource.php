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
     * Return activity metadata by the given user
     * @param string $userId
     * @return Response
     */
    public function indexByUser($userId=null)
    {
        return $this->indexByTypeAndUser(null, $userId);
    }


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
    
}
