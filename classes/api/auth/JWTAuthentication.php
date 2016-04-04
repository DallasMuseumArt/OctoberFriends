<?php namespace DMA\Friends\Classes\API\Auth;

use Exception;
use ArrayObject;
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
     * @param  array   $claimRules 
     *         Associative array where the key is the claim name and
     *         the value is a regex expression. e.g
     *         [ 'sub' => '/^friends\\|\\d+$/' ]
     *
     * @throws \DMA\Friends\Classes\API\Auth\Exceptions\JWTException
     *
     * @return array
     */
    public function decode($token, array $claimRules=null)
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
            throw new TokenExpiredException('Token has expired.');
        }
        
        if (! $this->validatedClaims($jws, $claimRules)){
           throw new TokenInvalidException('Token claims failed validation.');
        }
        
        $payload = (array) $jws->getPayload();
        
        if ($context = $payload['context']){
            $context = $this->toArrayObject($context);
            $payload['context'] = $context;
        }
        return $payload;
    }
    
    /**
     * Helper method to allow arrays to be access
     * like objects
     * @param mixed $data
     */
    protected function toArrayObject($data){
        if(is_array($data)) {
            $data = new ArrayObject($data);
            $data->setFlags(ArrayObject::ARRAY_AS_PROPS);
            foreach($data as $key => $value) {
                $data[$key] = $this->toArrayObject($value);
            }
        }
        return $data;
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
    
    /**
     * Validdate claims that match a regular expression
     *
     * @param $jws
     * @param  array   $claimRules 
     *         Associative array where the key is the claim name and
     *         the value is a regex expression. e.g
     *         [ 'sub' => '/^friends\\|\\d+$/' ]
     * @return bool
     */
    protected function validatedClaims($jws, array $claimRules = null)
    {
        $payload = $jws->getPayload();
        $claimRules = is_array($claimRules)?$claimRules:[];
        
        foreach ($claimRules as $key => $re){
            if(isset($payload[$key])) {
                $value = $payload[$key];
                if (!preg_match($re, $value)) {
                    // Claim daesn't match regex
                    return false;
                }
            }else{
                // Claim in not defined for that
                // reason validation should fail
                return false;
            }
            
        }
        
        return true;
    }
}