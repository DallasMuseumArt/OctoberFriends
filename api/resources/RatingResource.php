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
    //protected $transformer  = '\DMA\Friends\API\Transformers\ActivityTransformer';
    
   
    
    
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
        # TODO : stop default behaviour of the base resoure and 
        # return and error
        return Response::api()->errorForbidden();
        #return parent::index();
    }
    
    
}