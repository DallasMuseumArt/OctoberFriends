<?php namespace DMA\Friends\API\Resources;

use Response;
use DMA\Friends\Classes\API\BaseResource;
use DMA\Friends\Classes\PrintManager;
use RainLab\User\Models\User;
use DMA\Friends\Models\Location;
use DMA\Friends\Models\Reward;

class PrintResource extends BaseResource {

    /**
     * The following API actions in the UserResource are public.
     * It means API Authentication will not be enforce.
     * @var array
     */
    public $publicActions = ['card', 'reward'];
    
    /**
     * The listed actions check first if the
     * user can perform the action
     * @var array
     */
    public $checkAccessLevelActions= [
            'index', 'show', 'update', 'uploadAvatar',
            'userActivities', 'userRewards', 'userBadges'
    ];
    
    
    public function __construct()
    {
        // Add additional routes to Activity resource
        $this->addAdditionalRoute('card',  'card/{user}/{location}',            ['GET']);
        $this->addAdditionalRoute('reward','reward/{user}/{location}/{reward}',   ['GET']);
        
    }
    
    
    protected function doPrint($format, $userId, $locationId, array $extraArgs)
    {
        if(is_null($user = User::find($userId))){
            return Response::api()->errorNotFound('User not found');
        }
        
        if(is_null($location = Location::where('uuid', $locationId)->first())){
            return Response::api()->errorNotFound('Location not found');
        }
        
        $printManager = new PrintManager($location, $user);
        $result = null;
        
        switch (strtolower($format)){
            case 'card':
                $result = $printManager->printIdCard();
                break;
            
            case 'reward':
                $reward = array_get($extraArgs, 'reward');
                $result = $printManager->printCoupon($reward);
                break;
            
        }
        
        return $result;
    }
    
    public function card($userId, $locationId)
    {
        return $this->doPrint('card', $userId, $locationId, []);
    }

    public function reward($userId, $locationId, $rewardId)
    {
        if(is_null($reward = Reward::find($rewardId))){
            return Response::api()->errorNotFound('Reward not found');
        }
        
        return $this->doPrint('card', $userId, $locationId, [
                'reward' => $reward,
        ]);
    }
    
    public function index()
    {
        return Response::api()->errorForbidden();
    }   
    
    public function show($id)
    {
        return Response::api()->errorForbidden();
    }
    
}
