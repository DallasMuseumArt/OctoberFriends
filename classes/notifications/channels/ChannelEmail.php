<?php namespace DMA\Friends\Classes\Notifications\Channels;

use DMA\Friends\Classes\Notifications\Channels\Channel;
use DMA\Friends\Classes\Notifications\NotificationMessage;

/**
 * Channel to send email notification using default Laravel / OctoberCMS implemetation
 * @author Carlos Arroyo
 *
 */
class ChannelEmail implements Channel
{

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::getKey()
	 */
    public static function getKey()
	{
		return 'mail';
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::getDetails()
	 */
	public function getDetails()
	{
	    return [
	            'name'           => 'Email',
	            'description'    => 'Send notifications using OctoberCMS Mail.'
	    ];
	}	
	
	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::configChannel()
	 */
	public function configChannel()
	{
        // TODO : configure default send emails here
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::settingFields()
	 */
	public function settingFields()
	{
		// This channel don't requires settings
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::send()
	 */
	public function send(NotificationMessage $notification)
	{
	    $data = $notification->getData();
	    $view = $notification->getView();

	    if(\Mail::send($view, $data, function($message) use ($notification)
	    {
	        $user = $notification->getTo();
	    	$message->to($user->email, $user->name);
	    }) == 0){
	    	//throw new \Exception('Email notification was not send.');
	    }

	}
}
