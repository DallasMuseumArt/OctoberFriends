<?php namespace DMA\Friends\API\Resources;

use Session;
use Response;
use Request;
use Validator;

use DMA\Friends\Models\Reward;
use DMA\Friends\Classes\RewardManager;
use DMA\Friends\Classes\API\BaseResource;
use DMA\Friends\API\Transformers\UserProfileTransformer;

use RainLab\User\Models\User;


class RewardResource extends BaseResource {
  
    protected $model        = '\DMA\Friends\Models\Reward';
    protected $transformer  = '\DMA\Friends\API\Transformers\RewardTransformer';
    
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
        $this->addAdditionalRoute('redeemByGet',   'redeem/{reward}/user/{user}',   ['GET']);
        $this->addAdditionalRoute('redeemByPost',  'redeem/',                       ['POST']);
    }
    
    /**
     * @SWG\Definition(
     *      definition="response.redeem",
     *      required={"data"},
     *      @SWG\Property(
     *          property="data",
     *          type="object",
     *          ref="#/definitions/redeem.payload"
     *      )
     * )
     * 
     * @SWG\Definition(
     *      definition="redeem.payload",
     *      required={"success", "message", "user"},
     *      @SWG\Property(
     *          property="success",
     *          type="boolean"
     *      ),
     *      @SWG\Property(
     *          property="message",
     *          type="string"
     *      ),
     *      @SWG\Property(
     *          property="user",
     *          type="object",
     *          ref="#/definitions/user.info.points"
     *      ) 
     * )
     * 
     * 
     * @SWG\GET(
     *     path="rewards/redeem/{reward}/user/{user}",
     *     description="Redeem user points for rewards",
     *     summary="Redeem a reward",
     *     tags={ "rewards"},
     *
     *     @SWG\Parameter(
     *         ref="#/parameters/authorization"
     *     ),
     *     @SWG\Parameter(
     *         description="ID of reward to redeem",
     *         format="int64",
     *         in="path",
     *         name="reward",
     *         required=true,
     *         type="integer"
     *     ),
     *     @SWG\Parameter(
     *         description="ID of user",
     *         format="int64",
     *         in="path",
     *         name="user",
     *         required=true,
     *         type="integer"
     *     ),     
     *     @SWG\Response(
     *         response=200,
     *         description="Unsuccessful response",
     *         @SWG\Schema(ref="#/definitions/response.redeem")
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/response.redeem")
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
     *     )
     * )
     */
    public function redeemByGet($rewardId, $userId)
    {

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
        
        
        // Get common user points format via UserProfileTransformer
        $userTransformer = new UserProfileTransformer();
        $points = $userTransformer->getUserPoints($user);

        $payload = [
                'data' => [
                        'success' => $success,
                        'message' => $message,
                        'user' => [
                            'id'      => $user->getKey(),
                            'points'  => $points
                        ]
                ]
        ];
        
        return Response::api()->setStatusCode($httpCode)->withArray($payload);

    }
    
    /**
     * @SWG\Definition(
     *      definition="request.redeem",
     *      required={"reward_id", "user_id"},
     *      @SWG\Property(
     *          property="reward_id",
     *          type="integer",
     *          format="int32"
     *      ),
     *      @SWG\Property(
     *          property="user_id",
     *          type="integer",
     *          format="int32"
     *      )  
     * )
     * 
     * 
     * @SWG\Post(
     *     path="rewards/redeem",
     *     description="Redeem user points for rewards",
     *     summary="Redeem a reward",
     *     tags={ "rewards"},
     *
     *     @SWG\Parameter(
     *         ref="#/parameters/authorization"
     *     ),
     *     @SWG\Parameter(
     *         in="body",
     *         name="body",
     *         required=true,
     *         type="object",
     *         schema=@SWG\Schema(ref="#/definitions/request.redeem")
     *     ),
     *     
     *     @SWG\Response(
     *         response=200,
     *         description="Unsuccessful response",
     *         @SWG\Schema(ref="#/definitions/response.redeem")
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/response.redeem")
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
     *     )
     * )
     */
    public function redeemByPost()
    {
    
        $data = Request::all();
        $rules = [
                'reward_id'             => "required",
                'user_id'               => "required"
        ];
        
        $validation = Validator::make($data, $rules);
        if ($validation->fails()){
            return $this->errorDataValidation('Redeem data fails to validated', $validation->errors());
        }
        
        return $this->redeemByGet($data['reward_id'], $data['user_id']);
    
    }
    
    

    /**
     * @SWG\Get(
     *     path="rewards",
     *     description="Returns all rewards",
     *     summary="Returns all rewards",
     *     tags={ "rewards"},
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
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/reward.extended", type="array")
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
     *     path="rewards/{id}",
     *     description="Returns a reward by id",
     *     summary="Find a reward by id",
     *     tags={ "rewards"},
     *
     *     @SWG\Parameter(
     *         ref="#/parameters/authorization"
     *     ),
     *     @SWG\Parameter(
     *         description="ID of reward to fetch",
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
     *         @SWG\Schema(ref="#/definitions/reward.extended")
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
