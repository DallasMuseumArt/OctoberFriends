<?php namespace DMA\Friends\API\Resources;

use Session;
use Response;
use RainLab\User\Models\User;
use DMA\Friends\Classes\API\BaseResource;
use DMA\Friends\Activities\ActivityCode;
use DMA\Friends\Activities\LikeWorkOfArt;

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
                
                return [
                    'data' => [
                        'message' => $message
                    ]
                ];
                                     
            }
            
            return Response::api()->errorNotFound('User not found');
        } catch(Exception $e) {
            return Response::api()->errorInternalError($e->getMessage());   
        }
    }

}
