<?php namespace DMA\Friends\Classes\API\Auth\Middleware;

use Exception;
use Closure;
use Request;
use Response;
use FriendsAPI;
use FriendsAPIAuth;
use DMA\Friends\Classes\API\Auth\Exceptions\TokenInvalidException;

class FriendsApiAuthMiddleware
{
    public function handle($request, Closure $next)
    {
        try{
            $controllerAction   = array_get($request->route()->getAction(), 'uses', False);

            // Check if route can skip authentication
            $skip = FriendsAPI::isAuthenticationExcepted($controllerAction);
            if (!$skip){
                $token = $this->getToken();
                // Authenticated request using token.
                // If fails an exception will be rise
                $data = FriendsAPIAuth::authenticate($token);
                                
                // Inject into the request the authenticated user and
                // application in use
                $request->attributes->add(['tokenData' => $data]);
               
            }
                
            $response = $next($request);
            return $response;
        }catch(Exception $e){
            if($e instanceof TokenInvalidException){
                $errorResponse = 'errorUnauthorized';
            }else{
                $errorResponse = 'errorInternalError';
            }
            return Response::api()->{$errorResponse}($e->getMessage());
        }
    }
    
    
    private function getToken() 
    {
        if ($token = Request::header('Authorization',  Request::input('token', False))){
            
            // Drop Autherization prefix
            $prefix = 'bearer';
            $token = trim(str_ireplace($prefix, '', $token));
            
        }
        
        return $token;
    }
    
    
}