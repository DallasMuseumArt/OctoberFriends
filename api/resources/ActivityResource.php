<?php namespace DMA\Friends\API\Resources;

use Session;
use Request;
use Response;
use RainLab\User\Models\User;
use DMA\Friends\Classes\API\BaseResource;
use DMA\Friends\Activities\ActivityCode;
use DMA\Friends\Activities\LikeWorkOfArt;
use DMA\Friends\API\Transformers\UserProfileTransformer;

class ActivityResource extends BaseResource {

    protected $model        = '\DMA\Friends\Models\Activity';

    protected $transformer  = '\DMA\Friends\API\Transformers\ActivityTransformer';


    public function __construct()
    {
        // Add additional routes to Activity resource
        $this->addAdditionalRoute('checkin',       'checkin/{user}/',             ['POST']);
        $this->addAdditionalRoute('checkin',       'checkin/{user}/{code}',       ['GET']);
        $this->addAdditionalRoute('bulkCheckins',  'bulk-checkin/{user}/',        ['POST']);
        $this->addAdditionalRoute('bulkCheckins',  'bulk-checkin/{user}/{codes}', ['GET']);
    }


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
            // Trying if is a object assession number
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
            'message'           => $message
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
    
}
