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
        
        $data = AuthManager::auth($credentials);
        
        if ($data instanceof Model ){
            $tokenData = [ 'user_id' => $data->getKey() ];
            $token = $this->createToken($app, 'auth', $tokenData, $app->ttl);
            return ['user' => $data, 'token' => $token];
        } else if (is_array($data)) {
            $token = $this->createToken($app, 'verify', $data);
            return ['membership' => $data, 'token' => $token];
        }
        
        
        return null;

    }
    
    
    public function authenticate($token)
    {
        $payload = $this->decodeToken($token, 'auth');        
        $appKey  = array_get($payload, 'aud', Null);
        $context = array_get($payload, 'context', []);
        $userId  = array_get($context, 'user_id', Null);
        
        $app = $this->getAPIApplication($appKey);
        $user = User::find($userId);
        return ['app' => $app, 'user' => $user];
        
    }
    
    
    public function getAPIApplication($appKey)
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
        $tokenApp = array_get($tokenData, 'app', null);
        
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
    
    /**
     * 
     * @param unknown $app
     * @param unknown $sub
     * @param number $expMinutes
     * @param array $context
     * @return unknown
     */
    public function createToken($app, $tokenType, $tokenData=[], $expMinutes=null)
    {
        if (is_null($tokenType)) {
            throw new Exception('Token type is required.')  ; 
        }
        
        $date    = new Carbon();
        $payload = [
                'sub' => "friends|$tokenType|" . rand(),
                'aud' => $app->app_key,
                'iat' => $date->format('U'),
                'context' => $tokenData
        ];
    
        $exp = ($expMinutes)?$expMinutes:15; 
        
        if ( $exp ){
            $payload['exp'] = $date->copy()->addMinutes($exp)->format('U');
        }
    
    
        $token = $this->auth->encode($payload);
        return $token;
    }
    

    public function decodeToken($token, $tokenType, array $claimRules = [])
    {   
        $rules = array_merge([
                'sub' => "/^friends\\|$tokenType\\|\\d+$/"
        ], $claimRules);
        
        return $this->auth->decode($token, $rules);        
    }
        

        
}
