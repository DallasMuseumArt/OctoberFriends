<?php namespace DMA\Friends\Classes\API\Auth;

use Model;
use Config;
use Exception;
use Request;
use Carbon\Carbon;
use DMA\Friends\Classes\AuthManager;
use DMA\Friends\Classes\API\Auth\JWTAuthentication;
use DMA\Friends\Classes\API\Auth\Exceptions\TokenExpiredException;
use DMA\Friends\Classes\API\Auth\Exceptions\UserAccessDenied;
use DMA\Friends\Models\Application;
use RainLab\User\Models\User;

class APIAuthManager
{
    /**
     * 
     * @var Friends\Classes\API\Auth\JWTAuthentication
     */
    private $auth;
    
    
    public function __construct()
    {
        $this->registerAuthentication();
    }
    
    
    /**
     * Register authentication Token class
     */   
    private function registerAuthentication()
    {
        $secret = Config::get('dma.friends::secret', null);
        $algo   = Config::get('dma.friends::algo',   null);
        if (!is_null($secret) && !is_null($algo)){
            $this->auth = new JWTAuthentication($secret, $algo);
        }else{
            throw new Exception('API application key secret improperly configured');
        }

    }
    
    /**
     * 
     * @param array $credentials
     */
    public function attemp(array $credentials)
    {
        $appKey = array_get($credentials, 'app_key');
        $app = $this->getAPIApplication($appKey);
        
        $user = AuthManager::auth($credentials);
        if ($user){
            $token = $this->createToken($user, $app);
        }   
        
        return ['user' => $user, 'token' => $token];

    }
    
    
    public function authenticate($token)
    {
        $payload = $this->auth->decode($token);        
        $appKey  = array_get($payload, 'aud', Null);
        $context = array_get($payload, 'context', []);
        $userId  = array_get($context, 'user_id', Null);
        
        $app = $this->getAPIApplication($appKey);
        $user = User::find($userId);
        return ['app' => $app, 'user' => $user];
        
    }
    
    
    protected function getAPIApplication($appKey)
    {
        $app = Application::where('app_key', $appKey)->isActive()->first();
        $invalid = (!$app);
        $invalid = ($invalid)?:!$app->is_active;
        if ($invalid) {
            throw new Exception('Invalid application key');
        }
        return $app;
    }
    
    public function isApplicationKeyValid($appKey)
    {
        $app = $this->getAPIApplication($appKey);
        return (!$app);
    }
    
    /**
     * 
     * 
     * @param string $requestUser
     * @throws Exception
     */
    public function validatedUserAccess($requestUser=null)
    {
        $msg = '';
        $tokenData = Request::get('tokenData', []);
        $tokenUser = array_get($tokenData, 'user', null);
        $tokenApp  = array_get($tokenData, 'app', null);

        if ($tokenApp->access_level != Application::ACCESS_ALL_DATA){
             $denied = true;
             
             if ($tokenUser && $requestUser){
                if ($requestUser instanceof Model){
                    $requestUser = $requestUser->getKey();
                }
            
                // check if given $user is the same as the token
                // If not throw an exception
                $denied = !($tokenUser->getKey() == $requestUser);
            }
                
            if($denied){
                throw new UserAccessDenied();
            }
        }

        
    }
    
    public function createToken($user, $app)
    {
        $date    = new Carbon();
        $payload = [
            'sub' => 'friends|' . $user->getKey(), 
            'aud' => $app->app_key,
            'iat' => $date->format('U'), 
            'context' => [
                'user_id' => $user->getKey()     
            ]
        ];
        
        if ( $app->ttl ){
            $payload['exp'] = $date->copy()->addMinutes($app->token_ttl)->format('U');
        }
        
        
        $token = $this->auth->encode($payload);
        return $token;
    }
    
        
}
