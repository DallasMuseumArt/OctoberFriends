<?php namespace DMA\Friends\API\Resources;

use Session;
use Request;
use Response;
use RainLab\User\Models\User;
use DMA\Friends\Classes\API\BaseResource;
use DMA\Friends\Activities\ActivityCode;
use DMA\Friends\Activities\LikeWorkOfArt;
use DMA\Friends\API\Transformers\UserProfileTransformer;
use DMA\Friends\API\Resources\UserTransformerInjectionTrait;


class ActivityResource extends BaseResource {
    
    use UserTransformerInjectionTrait;
    
    protected $model        = '\DMA\Friends\Models\Activity';
    protected $transformer  = '\DMA\Friends\API\Transformers\ActivityTransformer';

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
        $this->addAdditionalRoute('checkin',       'checkin/{user}/',             ['POST']);
        $this->addAdditionalRoute('checkin',       'checkin/{user}/{code}',       ['GET']);
        $this->addAdditionalRoute('bulkCheckins',  'bulk-checkin/{user}/',        ['POST']);
        $this->addAdditionalRoute('bulkCheckins',  'bulk-checkin/{user}/{codes}', ['GET']);
    }

  
    /**
     * @SWG\Definition(
     *     definition="request.checkin",
     *     type="object",
     *     required={"code"},
     *     @SWG\Property(
     *         property="code",
     *         type="string"
     *     )
     * )
     * 
     * @SWG\Definition(
     *     definition="response.ok.single.checkin",
     *     description="Single response checking",
     *     type="object",
     *     required={"data"},
     *     @SWG\Property(
     *        property="data",
     *        type="object",
     *        ref="#/definitions/response.ok.activity.code.user"
     *     ),
     * )
     *     
     * @SWG\Definition(
     *     definition="response.failed.single.checkin",
     *     description="Single response checking",
     *     type="object",
     *     required={"data"},
     *     @SWG\Property(
     *        property="data",
     *        type="object",
     *        ref="#/definitions/response.failed.activity.code.user"
     *     ),
     * )
     * 
     * @SWG\Post(
     *     path="activities/checkin/{user}",
     *     description="Checkin user activities",
     *     summary="Checkin an activity",
     *     tags={ "activity"},
     *
     *     @SWG\Parameter(
     *         ref="#/parameters/authorization"
     *     ),
     *     @SWG\Parameter(
     *         description="ID of the user checking the activity",
     *         format="int64",
     *         in="path",
     *         name="user",
     *         required=true,
     *         type="integer"
     *     ),
     *
     *     @SWG\Parameter(
     *         description="Activity code or accessioned number",
     *         name="body",
     *         in="body",
     *         required=true,
     *         schema=@SWG\Schema(ref="#/definitions/request.checkin")
     *     ),
     *
     *     @SWG\Response(
     *         response=201,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/response.ok.single.checkin")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/response.failed.single.checkin")
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
     *    )
     * )
     *
     *
     * @SWG\Get(
     *     path="activities/checkin/{user}/{code}",
     *     description="Checkin user activities",
     *     summary="Checkin an activity",
     *     tags={ "activity"},
     *     
     *     @SWG\Parameter(
     *         ref="#/parameters/authorization"
     *     ),
     *     @SWG\Parameter(
     *         description="ID of the user checking the activity",
     *         format="int64",
     *         in="path",
     *         name="user",
     *         required=true,
     *         type="integer"
     *     ),
     *
     *     @SWG\Parameter(
     *         description="Activity code or accessioned number",
     *         in="path",
     *         name="code",
     *         required=true,
     *         type="string"
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/response.ok.single.checkin")
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/response.failed.single.checkin")
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
     *    )
     * )
     */
    
    
    public function checkin($user, $code=null)
    {

        $user = User::find($user);
        if (!is_null($user)){
            
            // Check if code is given in the URL  or is part of the body request
            if ( is_null($code) ){
                // Check if code is in the body of the request
                $data = Request::all();
                $code = array_get($data, 'code');
            }
            
            if ( is_null($code) ){
                $message = "'code' argument is required";
                return Response::api()->errorWrongArgs($message);
            }
            
            // Process activity code
            $response  = $this->processActivityCode($user, $code);
            $payload = [];
            
            $httpCode = array_get($response, 'http_code');
            $payload['data'] = array_get($response, 'payload');
            

            // Get user info
            $payload['data']['user'] = $this->getUserInfo($user);
            
            return Response::api()->setStatusCode($httpCode)->withArray($payload);

        }
        
        return Response::api()->errorNotFound('User not found');

    }

        
    /**
     * @SWG\Definition(
     *     definition="request.bulk.checkin",
     *     type="object",
     *     required={"codes"},
     *     @SWG\Property(
     *          type="array",
     *          items=@SWG\Schema(type="string"),
     *          property="codes",
     *     )
     * )
     * 
     * @SWG\Definition(
     *     definition="response.bulk.checkin",
     *     type="object",
     *     required={"data"},
     *     @SWG\Property(
     *        property="data",
     *        ref="#/definitions/response.bulk.activity.code.user"
     *     )
     * )
     *
     *
     * @SWG\Definition(
     *     definition="response.bulks.checkin",
     *     description="Single response checking",
     *     type="object",
     *     required={"data"},
     *     @SWG\Property(
     *        property="data",
     *        type="object",
     *        ref="#/definitions/response.activity.code.user"
     *     ),
     * )
     *
     * @SWG\Post(
     *     path="activities/bulk-checkin/{user}",
     *     description="Bulk checkin user activities",
     *     summary="Bulk activity checkin",
     *     tags={ "activity"},
     *
     *     @SWG\Parameter(
     *         ref="#/parameters/authorization"
     *     ),
     *     @SWG\Parameter(
     *         description="ID of the user checking the activity",
     *         format="int64",
     *         in="path",
     *         name="user",
     *         required=true,
     *         type="integer"
     *     ),
     *
     *     @SWG\Parameter(
     *         description="Activity codes and/or accessioned number",
     *         name="body",
     *         in="body",
     *         required=true,
     *         schema=@SWG\Schema(ref="#/definitions/request.bulk.checkin")
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/response.bulk.checkin", type="array")
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
     *    )
     * )
     *
     *
     * @SWG\Get(
     *     path="activities/bulk-checkin/{user}/{codes}",
     *     description="Bulk checkin user activities",
     *     summary="Bulk activity checkin",
     *     tags={ "activity"},
     *
     *     @SWG\Parameter(
     *         ref="#/parameters/authorization"
     *     ),
     *     @SWG\Parameter(
     *         description="ID of the user checking the activity",
     *         format="int64",
     *         in="path",
     *         name="user",
     *         required=true,
     *         type="integer"
     *     ),
     *
     *     @SWG\Parameter(
     *         description="Activity codes and/or accessioned numbers",
     *         in="path",
     *         name="codes",
     *         type="array",
     *         required=true,
     *         items=@SWG\Schema(type="string"),
     *         collectionFormat="csv",
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/response.bulk.checkin", type="array")
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
     *    )
     * )
     */
    
    
    public function bulkCheckins($user, $codes=null)
    {

        $user = User::find($user);
        if (!is_null($user)){

            // Check if codes are given in the URL or are part of the body request
            if (is_null($codes)){
                // Check if codes are in the body of the request
                $data = Request::all();
                $codes = array_get($data, 'codes');
            }

            if ( is_null($codes) ){
                $message = "'codes' argument is required";
                return Response::api()->errorWrongArgs($message);
            }
            
            
            // Convert into array if necessary
            if (is_string($codes)){
                $codes = explode(',',$codes);
            }
            
            
            $payload = [];
            foreach ($codes as $code){
                // Process activity code
                $code      = trim($code); 
                $response  = $this->processActivityCode($user, $code);

                $httpCode = array_get($response, 'http_code');
                $activity = array_get($response, 'payload');
                
                $payload['data']['checkins'][] = array_merge(['http_code' => $httpCode] , $activity);;
            }        
    
            // Get user info
            $payload['data']['user'] = $this->getUserInfo($user);
             
            $httpCode = 200;
            return Response::api()->setStatusCode($httpCode)->withArray($payload);
    
        }
    
        return Response::api()->errorNotFound('User not found');
       
    }
    
    /**
     * @SWG\Definition(
     *     definition="response.ok.activity.code",
     *     type="object",
     *     required={"success", "activity_code", "activity_points", "message", "feedback_message", "complete_message", "data"},
     *     @SWG\Property(
     *         property="success",
     *         type="boolean",
     *         default="true"
     *     ), 
     *     @SWG\Property(
     *         property="http_code",
     *         type="integer",
     *         format="int32",
     *         enum={200, 201},
     *         default=201
     *     ),  
     *      
     *     @SWG\Property(
     *         property="activity_code",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         property="activity_points",
     *         type="integer",
     *         format="int32"
     *     ),      
     *     @SWG\Property(
     *         property="message",
     *         type="string"
     *     ),  
     *     @SWG\Property(
     *         property="feedback_message",
     *         type="string"
     *     ),  
     *     @SWG\Property(
     *         property="complete_message",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         description="Extra activity data send back when the activity is successful",
     *         property="data",
     *         type="object"
     *     )        
     * )
     * 
     */
    
    /**
     * @SWG\Definition(
     *     definition="response.failed.activity.code",
     *     type="object",
     *     required={"success", "activity_code", "activity_points", "message", "feedback_message", "complete_message"},
     *     @SWG\Property(
     *         property="success",
     *         type="boolean"
     *     ),
     *     @SWG\Property(
     *         property="http_code",
     *         type="integer",
     *         format="int32",
     *         enum={200, 201},
     *         default=200
     *     ),
     *
     *     @SWG\Property(
     *         property="activity_code",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         property="activity_points",
     *         type="integer",
     *         format="int32"
     *     ),      
     *     @SWG\Property(
     *         property="message",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         property="feedback_message",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         property="complete_message",
     *         type="string"
     *     )
     * )
     *
     */
     
    // TODO: This model definition should extend from "response.ok.activity.code" 
    // But it seems model extension is not working correclty swagger-ui  
    /**
     * 
     * @SWG\Definition(
     *     definition="response.failed.activity.code.user",
     *     type="object",
     *     required={"success", "activity_code", "message", "feedback_message", "complete_message", "user"},
     *     @SWG\Property(
     *         property="success",
     *         type="boolean"
     *     ), 
     *     @SWG\Property(
     *         property="http_code",
     *         type="integer",
     *         format="int32",
     *         enum={200, 201},
     *         default=200
     *     ),  
     *     @SWG\Property(
     *         property="activity_code",
     *         type="string"
     *     ), 
     *     @SWG\Property(
     *         property="activity_points",
     *         type="integer",
     *         format="int32"
     *     ), 
     *     @SWG\Property(
     *         property="message",
     *         type="string"
     *     ),  
     *     @SWG\Property(
     *         property="feedback_message",
     *         type="string"
     *     ),  
     *     @SWG\Property(
     *         property="complete_message",
     *         type="string"
     *     ),     
     *     @SWG\Property(
     *         property="user",
     *         ref="#/definitions/user.info.points"
     *     )    
     * 
     * )
     *
     */
    
    /**
     *
     * @SWG\Definition(
     *     definition="response.ok.activity.code.user",
     *     type="object",
     *     required={"success", "activity_code", "activity_points", "message", "feedback_message", "complete_message", "user", "data"},
     *     @SWG\Property(
     *         property="success",
     *         type="boolean"
     *     ),
     *     @SWG\Property(
     *         property="http_code",
     *         type="integer",
     *         format="int32",
     *         enum={200, 201},
     *         default=201
     *     ),
     *     @SWG\Property(
     *         property="activity_code",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         property="activity_points",
     *         type="integer",
     *         format="int32"
     *     ),
     *     @SWG\Property(
     *         property="message",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         property="feedback_message",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         property="complete_message",
     *         type="string"
     *     ),
     *     @SWG\Property(
     *         property="user",
     *         ref="#/definitions/user.info.points"
     *     ),
     *     @SWG\Property(
     *         description="Extra activity data send back when the activity is successful",
     *         property="data",
     *         type="object"
     *     )  
     * )
     *
     */
    
    /**
     * @SWG\Definition(
     *     definition="response.bulk.activity.code.user",
     *     type="object",
     *     required={"checkins", "user"},
     *     @SWG\Property(
     *        type="array",
     *        property="checkins",
     *        items=@SWG\Schema(ref="#/definitions/response.ok.activity.code")  
     *     ),
     *     @SWG\Property(
     *        property="user",
     *        ref="#/definitions/user.info.points"   
     *     )         
     *
     * )
     */

    /**
     * 
     * @param RainLab\User\Models\User  $user
     * @param string $code
     * @return Array
     * Return an associative code with a intended HTTP Code
     * and an Array with information of the activity code  
     * 
     */
    protected function processActivityCode ($user, $code)
    {
        $params = [];
        // Get code from message
        $params['code'] = $code;
        
        // process Activity code first
        if (!$activity = ActivityCode::process($user, $params)) {
            // Not found activity with that code.
            // Trying if is an object  number
            $activity = LikeWorkOfArt::process($user, $params);
        }
        
        // Determine the content of the message
        $holder = ( $activity ) ? 'activityMessage' : 'activityError';
        $message = Session::pull($holder);
        
        
        if (is_array($message)) {
            $message = implode('\n', array_filter($message));
        }
        
        $payload = [
            'success'           => ($activity) ? true : false,
            'activity_code'     => $code,
            'activity_points'   => ( $activity ) ? $activity->points : null,
            'message'           => $message,
            'feedback_message'  => ( $activity ) ? $activity->feedback_message : null,
            'complete_message'  => ( $activity ) ? $activity->complete_message : null
        ];
        
        $httpCode = 200;
        
        // Check if is not a boolean if not is because is an intance of an activity
        if( !is_bool($activity) ) {
            // Check if and artwork is attached to the activity
            // if so this activity is a LikeWorkOfArt
            $objectData = (array_get($activity, 'objectData', null));
            if(!is_null($objectData)) {
                $httpCode = 201;
                $payload['data']['artwork'] = $objectData;
            }
        }
        
        return [ 'http_code' => $httpCode, 'payload' => $payload ];
        
    }
 
    
    /**
     * Get user  information
     * @param RainLab\User\Models\User $user
     * @return array
     */
    protected function getUserInfo($user)
    {

        // Get common user points format via UserProfileTransformer
        $userTransformer = new UserProfileTransformer();
        $points = $userTransformer->getUserPoints($user);
        
        return [ 
            'id'        => $user->getKey(),
            'points'    => $points
        ];
    }
    
    /**
     * @SWG\Get(
     *     path="activities",
     *     description="Returns all activities",
     *     summary="Returns all activities",
     *     tags={ "activity"},
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
     *     @SWG\Parameter(
     *         ref="#/parameters/sort"
     *     ),
     *     
     *     @SWG\Parameter(
     *         description="Include a completed steps for a given user on each activity",
     *         format="int64",
     *         name="user",
     *         in="query",
     *         type="integer",
     *         required=false
     *     ), 
     *          
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/activity.extended", type="array")
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
     *     path="activities/{id}",
     *     description="Returns an activity by id",
     *     summary="Find activity by id",
     *     tags={ "activity"},
     *
     *     @SWG\Parameter(
     *         ref="#/parameters/authorization"
     *     ),
     *     @SWG\Parameter(
     *         description="ID of activity to fetch",
     *         format="int64",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *
     *     @SWG\Parameter(
     *         description="Include a completed steps for a given user on each activity",
     *         format="int64",
     *         name="user",
     *         in="query",
     *         type="integer",
     *         required=false
     *     ), 
     * 
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/activity.extended")
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
