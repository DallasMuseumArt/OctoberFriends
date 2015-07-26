<?php namespace DMA\Friends\Classes\Mailchimp;

use Log;
use closure;
use GuzzleHttp;
use Exception;
use Illuminate\Support\Collection;
use DMA\Friends\Classes\Mailchimp\MailchimpResponse;
use DMA\Friends\Classes\Mailchimp\MailchimpException;


/**
 * Basic client to access Mailchimp api. 
 * All calls are asynchronous therefore they return a promise.
 * 
 * @author carroyo
 *
 */
class BaseMailchimpClient
{

    /**
     * Notes:
     * Use member status as follow
     *  - To send a confirmation email, use 'pending'.
     *  - To archive unused meembers, use 'unsubscribed' or 'cleaned'.
     */
    
    const MEMBER_STATUS_SUBSCRIBED   = 'subscribed';
    const MEMBER_STATUS_UNSUBSCRIBED = 'unsubscribed';
    const MEMBER_STATUS_CLEANED      = 'cleaned';
    const MEMBER_STAUTS_PENDING      = 'pending';
    
    
    private $client;
    private $baseUrl = "https://%s.api.mailchimp.com/3.0/";
    private $allowedMethods = ['get', 'head', 'put', 'post', 'patch', 'delete'];
    
    

    /**
    * @param $apiKey stirng Mailchimp api key to user
    */
    public function __construct($apiKey)
    {
        try{
            $this->dataCenter = explode('-', $apiKey);
            $this->dataCenter = $this->dataCenter[1];
            $this->apiKey = $apiKey;
            $this->baseUrl = sprintf($this->baseUrl, $this->dataCenter);
        }catch(Exception $e){
            throw new Exception('API key is invalid');
        }

    }


    protected function getClient()
    {
        if (!$this->client) {
            $this->client = new GuzzleHttp\Client([
                  'headers'  => [ 'Authorization' => 'apikey ' . $this->apiKey ],
            ]);
        }
        return $this->client;
    }

    /**
     * Test Mailchip connection
     * @return \DMA\Friends\Classes\Mailchimp\MailchimpResponse
     */
    public function testConnection()
    {
        $client = $this->getClient();
        $response = $client->get('');
        return new MailchimpResponse($response);      
    }

    /**
    * Helper function to get Member identification
    * @param $email User email address
    * @return string
    */
    public function getMemberID( $email )
    {
        // sanatize email address
        $email = strtolower(trim($email));
        return md5($email);
    }

    public function request( $verb, $endpoint, array $params=[] )
    {      
        try{
            // Get Client
            $client = $this->getClient();
            
            $options = [];
            
            if($params && $verb != 'GET')
            {
                $options['json']  = $params;
            }else{
                $options['query'] = $params;
            }
            
            // Final endpoint. I don't use base_uri option because it keeps choping 
            // the version path from the final url
            $endpoint = $this->baseUrl . $endpoint;
            
            $method = strtolower(trim($verb)) . 'Async';
            
            // Pass current instance to clouse
            $promise = $client->{$method}($endpoint, $options)->then(
                    function ($response){
                        return new MailchimpResponse($response);
                    },
                    function ($e) use ($endpoint, $verb, $options) {
                        $response = $e->getResponse();
                        $response = new MailchimpResponse($response);
                        $exception = new MailchimpException($response, $e);

                        $msg = 'Mailchimp [ ' . $exception->getCode() . ' ] : ' . $exception->getMessage().  ' via '. $verb . ' to ' . $endpoint;
                        Log::error($msg, $options); 
                        
                        throw $exception;
                        
                    }
            );
            
            // return promise
            return $promise;//->wait();
            
            
        }catch( Exception $e ){
            // Execute call sending response of why the request failed
            $msg = 'Unexpected exception raised when calling Mailchip API : ' . $e;
            Log::debug($msg);
            //throw $e;
        }
       

    }
    
    
    /**
     * @param string $method
     * @param array $arguments
     * @return Collection
     * @throws Exception
     */
    public function __call($method, $arguments)
    {

        if (count($arguments) < 1) {
            throw new InvalidArgumentException('URI endpoint is required');
        }
        if ( ! in_array($method, $this->allowedMethods)) {
            throw new BadMethodCallException('Method "' . $method . '" is not supported.');
        }
        $endpoint = $arguments[0];
        $options  = isset($arguments[1]) ? $arguments[1] : [];
        $callback = isset($arguments[2]) ? $arguments[2] : NULL;
        $method   = strtoupper($method);
        
        return $this->request($method, $endpoint, $options, $callback);
    }



}

