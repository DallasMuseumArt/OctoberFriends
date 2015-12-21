<?php namespace DMA\Friends\API\Resources;

use Response;
use DMA\Friends\Classes\API\BaseResource;
use DMA\Friends\Classes\PrintManager;
use RainLab\User\Models\User;
use DMA\Friends\Models\Location;
use DMA\Friends\Models\Reward;



class PrintResource extends BaseResource {

    
    public function __construct()
    {
        // Add additional routes to Activity resource
        $this->addAdditionalRoute('card',  'card/{user}/{location}',              ['GET']);
        $this->addAdditionalRoute('reward','reward/{user}/{location}/{reward}',   ['GET']);
        
    }
    
    /**
     * Send to printer
     * 
     * @param string $format
     * @param integer $userId
     * @param string $locationId
     * @param array $extraArgs
     */
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
    
    /**
     * @SWG\Get(
     *     path="print/card/{user}/{location}",
     *     description="Send to location printer a member card",
     *     summary="Print member card",
     *     tags={ "print"},
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
     *     @SWG\Parameter(
     *         description="ID of the location requesting print",
     *         in="path",
     *         name="location",
     *         required=true,
     *         type="string"
     *     ),        
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response"
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
    
    public function card($userId, $locationId)
    {
        return $this->doPrint('card', $userId, $locationId, []);
    }

    /**
     * @SWG\Get(
     *     path="print/reward/{user}/{location}/{reward}",
     *     description="Send to location printer a reward",
     *     summary="Print a reward",
     *     tags={ "print"},
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
     *     @SWG\Parameter(
     *         description="ID of the location requesting print",
     *         in="path",
     *         name="location",
     *         required=true,
     *         type="string"
     *     ),  
     *     @SWG\Parameter(
     *         description="ID of the reward to print",
     *         format="int64",
     *         in="path",
     *         name="reward",
     *         required=true,
     *         type="integer"
     *     ),  
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response"
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
    
    public function reward($userId, $locationId, $rewardId)
    {
        if(is_null($reward = Reward::find($rewardId))){
            return Response::api()->errorNotFound('Reward not found');
        }
        
        return $this->doPrint('reward', $userId, $locationId, [
                'reward' => $reward,
        ]);
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
