<?php namespace DMA\Friends\API\Resources;

use Session;
use Response;
use DMA\Friends\Models\Reward;
use DMA\Friends\Classes\RewardManager;
use DMA\Friends\Classes\API\BaseResource;

use RainLab\User\Models\User;


class RewardResource extends BaseResource {

    protected $model        = '\DMA\Friends\Models\Reward';

    protected $transformer  = '\DMA\Friends\API\Transformers\RewardTransformer';

    public function __construct()
    {
        // Add additional routes to Activity resource
        $this->addAdditionalRoute('redeem',   'redeem/{reward}/user/{user}',   ['GET', 'POST']);
    }
    
    public function redeem($rewardId, $userId)
    {
        try{
            
            if(is_null($user = User::find($userId))){
                return Response::api()->errorNotFound('User not found');
            }
            
            if(is_null($reward = Reward::find($rewardId))){
                return Response::api()->errorNotFound('Reward not found');
            }
            
            RewardManager::redeem($rewardId, $user);
            
            // Check if redeem was successful 
            $message = Session::pull('rewardMessage');
            $type    = ($message) ? 'info' : 'error'; 
            
            $success = true;
            $httpCode = 201;
            
            
            if($type == 'error'){
                $success = false;
                $httpCode = 200;
                $message = Session::pull('rewardError');
            }
            
    
            $payload = [
                    'data' => [
                            'success' => $success,
                            'message' => $message
                    ]
            ];
            
            return Response::api()->setStatusCode($httpCode)->withArray($payload);
            
             
        } catch(Exception $e) {
            return Response::api()->errorInternalError($e->getMessage());
        }
    }
    
    
}
