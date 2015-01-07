<?php namespace DMA\Friends\Classes\Notifications\Channels;

use Log;
use Mandrill;
use Mandrill_Error;
use DMA\Friends\Models\Settings;
use DMA\Friends\Classes\Notifications\Channels\Channel;
use DMA\Friends\Classes\Notifications\NotificationMessage;

/**
 * Channel to send email notification using default Laravel / OctoberCMS implemetation
 * @author Carlos Arroyo
 *
 */
class ChannelMandrill implements Channel
{

    private $client;
    private $from_mail;
    private $from_name;
    private $reply_to;
    
	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::getKey()
	 */
    public function getKey()
	{
		return 'mandrill';
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::getDetails()
	 */
	public function getDetails()
	{
	    return [
	            'name'           => 'Mandrill ( MailChimp )',
	            'description'    => 'Send email via Mandrill and register user activity in MailChimp.'
	    ];
	}	
	
	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::configChannel()
	 */
	public function configChannel()
	{
	    $apiKey     = Settings::get('mandrill_api_key');
	    
	    $this->from_mail  = Settings::get('mandrill_from_name');
	    $this->from_name  = Settings::get('mandrill_from_mail');
	    $this->reply_to   = Settings::get('mandrill_reply_to');

        if ($apiKey){
	       $this->mandrill = new Mandrill($apiKey);
        }
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::settingFields()
	 */
	public function settingFields()
	{
		return [
            'mandrill_api_key' => [
                    'label' => 'Mandrill API Key',
                    'span'  => 'auto'
		    ],
	        'mandrill_from_name' => [
	                'label' => 'Default from name',
	                'span'  => 'auto'
	        ],
	        'mandrill_from_mail' => [
	                'label' => 'Default from email',
	                'span'  => 'auto'
	        ],	
	        'mandrill_reply_to' => [
	                'label' => 'Reply To',
	                'span'  => 'auto'
	        ],		        	        
		        
		];
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::send()
	 */
	public function send(NotificationMessage $notification)
	{
	   try {

            $template_name      = $notification->getView();
            $template_content   = $notification->getData();

            $user = $notification->getTo();
            
            $message = [
                'from_email' => $this->from_mail,
                'from_name'  => $this->from_name,
                'to' => [
                    [
                        'email' => $user->email,
                        'name'  =>  $user->name,
                        'type'  => 'to'
                    ]
                ],
                'headers' => ['Reply-To' => $this->reply_to],
                'important' => false,
                'track_opens' => true,
                'track_clicks' => true,
                'auto_text' => true,
                'auto_html' => true,
                'inline_css' => null,
                'url_strip_qs' => null,
                'preserve_recipients' => false,
                'view_content_link' => null,
            ];
            
            $async = false;
            $result = $mandrill->messages->sendTemplate($template_name, $template_content, $message, $async);
            print_r($result);
            /*
            Array
            (
                [0] => Array
                    (
                        [email] => recipient.email@example.com
                        [status] => sent
                        [reject_reason] => hard-bounce
                        [_id] => abc123abc123abc123abc123abc123
                    )
            
            )
            */
        } catch(Mandrill_Error $e) {
            Log::error(sprintf('A mandrill error occurred:  %s', $e));
        }

	}
}
