<?php namespace DMA\Friends\Classes\Notifications\Channels;

use Log;
use Event;
use Services_Twilio;
use DMA\Friends\Models\Settings;
use DMA\Friends\Classes\Notifications\Channels\Channel;
use DMA\Friends\Classes\Notifications\Channels\Listenable;
use DMA\Friends\Classes\Notifications\Channels\Webhook;
use DMA\Friends\Classes\Notifications\NotificationMessage;
use DMA\Friends\Classes\Notifications\IncomingMessage;


/**
 * Channel for sending SMS notification using Twilio as gateway
 * @author Carlos Arroyo
 *
 */
class ChannelSMS implements Channel, Listenable, Webhook
{
    private $client;
    private $fromNumber;

	public function getKey()
	{
		return 'sms';
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::getDetails()
	 */
	public function getDetails()
	{
	    return [
	            'name'           => 'SMS',
	            'description'    => 'Send notifications by SMS using Twilio.'
	    ];
	}
	
	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::configChannel()
	 */
	public function configChannel()
	{
	    // TOOD : get this values for OctoberCMS settings
	    $accountSid = Settings::get('twilio_account_id');
	    $authToken  = Settings::get('twilio_auth_token');	    
	    $this->fromNumber = $this->cleanPhone(Settings::get('twilio_default_from_number'));

	    $this->client = new Services_Twilio($accountSid, $authToken);
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::settingFields()
	 */
	public function settingFields()
	{
		return [
            'twilio_account_id' => [
                'label' => 'Twilio account ID',
                'span'  => 'auto'
		    ],
		    'twilio_auth_token' => [
		         'label' => 'Twilio authentication token',
		         'span'  => 'auto'
		    ],
		    'twilio_default_from_number' => [
		         'label' => 'Twilio default from number',
		         'span'  => 'auto'
		    ],
		];

	}

	/**
	 * Clean phone number for Twilio
	 * @param string $phone
	 * @return string
	 */
	protected function cleanPhone($phone)
	{
	    $phone = preg_replace('/\\D+/', '', $phone);
	    if(!empty($phone)){
	        return '+' . $phone;
	    }
	    return '';
	}
	
	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::send()
	 */
	public function send(NotificationMessage $message)
	{

	    // TODO : add validation to control the size of the message.
	    $toUser  = $message->getTo();
	    $data    = $message->getData();
	    $txt     = $message->getContent();

	    // Clean phone user
	    $toPhone = $this->cleanPhone($toUser->phone);
	    if(!empty($toPhone)){
    	    $sms = $this->client->account->sms_messages->create(
    	    		$this->fromNumber, // From a Twilio number in your account
    	    		$toPhone, // Text any number
    	    		$txt
    	    );
	    }
	   
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Listenable::readChannel()
	 */
	public function read()
	{
        return [];
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Webhook::webhook()
	 */
	public function webhook(array $request)
	{
	    $httpCode = 200;
	    try
	    {
	        $msg = new IncomingMessage($this->getKey());
	        $msg->from($request['From']);
	        $msg->setContent($request['Body']);

	        // TODO : this should be reusable for other channels
	        $key = $this->getKey();
	        $event = strtolower("dma.channel.$key.incoming.data");
	        Event::fire($event, [[$msg]]);
	        Log::debug('Processed Twilio incoming SMS', $msg->getData());
	    }
	    catch(\Exception $e)
	    {
    	    Log::error('Processing Twilio webhook request', $request);
	        Log::error(sprintf('Processing Twilio webhook:  %s', $e));
	        $httpCode = 500;
	    }


	    // Response message was read ok
	    return \Response::make('<Response></Response>', $httpCode);
	}

}
