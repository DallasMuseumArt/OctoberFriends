<?php namespace DMA\Friends\Classes\API\Auth;

use Exception;
use Namshi\JOSE\JWS;
use DMA\Friends\Classes\API\Auth\Exceptions\TokenInvalidException;
use DMA\Friends\Classes\API\Auth\Exceptions\TokenExpiredException;


class JWTAuthentication
{
    /**
     * @var \Namshi\JOSE\JWS
     */
    
    protected $jws;
    
    
    /**
     * @var string 
     */
    private $secret;
    
    /**
     * @var string
     */
    private $algo;
        
    
    /**
     * @param string  $secret
     * @param string  $algo
     */
    public function __construct($secret, $algo)
    {
        $this->secret = $secret;
        $this->algo   = $algo;
        $this->jws    = new JWS([
            'typ' => 'JWT', 
            'alg' => $algo
        ]);
    }
    
    /**
     * Create a JSON Web Token
     *
     * @throws \DMA\Friends\Classes\API\Auth\Exceptions\JWTException
     *
     * @return string
     */
    public function encode(array $payload)
    {
        try {
            if (!isset($payload['iat'])) {
                $now            = new \DateTime('now');
                $payload['iat'] = $now->format('U');
            }
            
            $this->jws->setPayload($payload)
                 ->sign($this->secret);
            return (string) $this->jws->getTokenString();
            
        } catch (Exception $e) {
            throw new Exception('Could not create token: ' . $e->getMessage());
        }
    }
    
    /**
     * Decode a JSON Web Token
     *
     * @param  string  $token
     *
     * @throws \DMA\Friends\Classes\API\Auth\Exceptions\JWTException
     *
     * @return array
     */
    public function decode($token)
    {
        try {
            // let's never allow unsecure tokens
            $jws = $this->jws->load($token, false);
        } catch (Exception $e) {
            throw new TokenInvalidException($e->getMessage());
        }
        
        if (! $jws->verify($this->secret, $this->algo)) {
            throw new TokenInvalidException('Token Signature could not be verified.');
        }
        
        if ($this->isExpired($jws)){
            throw new TokenExpiredException('Token has expired. You need to re-authenticate again.');
        }
        
        return (array) $jws->getPayload();
    }
    
    
    /**
     * Checks whether the token is expired based on the 'exp' value.
     *it
     * @return bool
     */
    protected function isExpired($jws)
    {
        $payload = $jws->getPayload();
        if (isset($payload['exp']) && is_numeric($payload['exp'])) {
            $now = new \DateTime('now');
            return ($now->format('U') - $payload['exp']) > 0;
        }
        return false;
    }
    

    
    
    
}