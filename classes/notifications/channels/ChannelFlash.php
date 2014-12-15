<?php namespace DMA\Friends\Classes\Notifications\Channels;

use Log;
use Flash;
use DMA\Friends\Classes\Notifications\Channels\Channel;
use DMA\Friends\Classes\Notifications\NotificationMessage;
use DMA\Friends\Classes\Notifications\IncomingMessage;



/**
 * Channel to push notifications through OctoberCMS Flash messaging 
 * @author Carlos Arroyo
 *
 */
class ChannelFlash implements Channel
{

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::getKey()
	 */
    public function getKey()
	{
		return 'flash';
	}

    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\Notifications\Channels\Channel::configChannel()
     */
	public function configChannel()
	{
        // This channel don't requires configurations
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::settingFields()
	 */
	public function settingFields()
	{
		/*
	    return [
            'dummy_var' => [
                'label' => 'Dummy variable',
                'span'  => 'auto'
		    ],
		];
		*/

	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::send()
	 */
	public function send(NotificationMessage $message)
	{
	    $message = $message->getContent();

	    // TODO : Allow to set the type of Flash message. 
	    // Maybe a variable use in NotificationMessage
	     
	    Flash::info($message);
    
	}

  
}
