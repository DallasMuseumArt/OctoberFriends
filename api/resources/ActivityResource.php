<?php namespace DMA\Friends\API\Resources;

use Session;
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
        $this->addAdditionalRoute('checkin', 'checkin/{user}/{code}', ['GET','POST']);
    }


    public function checkin($user, $code)
    {
        try{
            $user = User::find($user);
            $result = [];
            if (!is_null($user)){
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
                
                // Get common user points format via UserProfileTransformer
                $userTransformer = new UserProfileTransformer();
                $points = $userTransformer->getUserPoints($user);
                
                
                $payload = [
                    'data' => [
                        'success' => ($activity) ? true : false,    
                        'message' => $message,
                        'user' => [
                                'id'      => $user->getKey(),
                                'points'  => $points
                        ]
                    ]
                ];
                
                $httpCode = 200;
                
                // check if is not a boolean if not is because is an intance of an activity
                if( !is_bool($activity) ) {
                    // Check if and artwork is attached to the activity
                    // if so this activity is a LikeWorkOfArt
                    $objectData = (array_get($activity, 'objectData', null));
                    if(!is_null($objectData)) {
                        $httpCode = 201;
                        $payload['data']['artwork'] = $objectData;
                    }
                }

                
                return Response::api()->setStatusCode($httpCode)->withArray($payload);

            }
            
            return Response::api()->errorNotFound('User not found');
        } catch(Exception $e) {
            return Response::api()->errorInternalError($e->getMessage());   
        }
    }

}
