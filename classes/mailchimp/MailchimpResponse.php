<?php namespace DMA\Friends\Classes\Mailchimp;

use Illuminate\Support\Collection;

class MailchimpResponse
{
    /**
     * @var integer HTTP code returned by Mailchimp 
     */
    public $code;
    
    /**
     * @var array Decoded json data returned
     */
    public $data;
    
    /**
     * @var GuzzleHttp\Response
     */
    public $response;
    
    /**
     * Class to process and pass Mandril API resonse
     * @param unknown $response Guzzle HTTP API response
     */
    public function __construct($response)
    {
        
        $this->processResponse($response);
    }
    
    protected function processResponse($response)
    {
    
        $data = new Collection(
            json_decode($response->getBody())
        );
        if ($data->count() == 1) {
            $data = $data->collapse();
        }
    
        $this->data = $data;
        $this->code = intval($response->getStatusCode());
        $this->response = $response;

    }
    
}
