<?php namespace DMA\Friends\Classes\Mailchimp;

use Exception;
use DMA\Friends\Classes\Mailchimp\MailchimpResponse;


class MailchimpException extends Exception
{
    
    /**
     * @var DMA\Friends\Classes\Mailchimp\MailchimpResponse
     */
    public $response;
    
    public function __construct(MailchimpResponse $response, $previous)
    {
        $code    = $response->code;
        $message = @$response->data['title'];
        parent::__construct($message, $code, $previous);
        
    }
    
}
