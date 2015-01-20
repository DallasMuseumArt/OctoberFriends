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
    public static function getKey()
	{
		return 'flash';
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::getDetails()
	 */
	public function getDetails()
	{
	    return [
	            'name'           => 'Flash message',
	            'description'    => 'Send notifications by OctoberCMS Flash messaging.'
	    ];
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
	    try{
    	    $content = (string)$message->getContent();
    	    
    	    if(!empty($content)){
    	        $viewSettings  = $message->getViewSettings();   	        
    	        $type          = @$viewSettings['type'];

    	        if(!in_array($type, ['info', 'success', 'error'])){
    	           $type = 'info';
    	        }

    	       //Flash::$type($content);
    	       Flash::add($type, $content);

    	    }
	    }catch(\Exception $e){
	        Log::error(sprintf('Sending a Flash notification failed:  %s', $e));
	    }
	    
	}

  
}
