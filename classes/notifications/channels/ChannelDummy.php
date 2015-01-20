<?php namespace DMA\Friends\Classes\Notifications\Channels;

use Log;
use DMA\Friends\Classes\Notifications\Channels\Channel;
use DMA\Friends\Classes\Notifications\Channels\Listenable;
use DMA\Friends\Classes\Notifications\NotificationMessage;
use DMA\Friends\Classes\Notifications\IncomingMessage;



/**
 * Channel used for testing
 * @author Carlos Arroyo
 *
 */
class ChannelDummy implements Channel, Listenable
{
    
	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::getKey()
	 */
    public static function getKey()
	{
		return 'dummy';
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::getDetails()
	 */
	public function getDetails()
	{
	    return [
	            'name'           => 'Dummy',
	            'description'    => 'Channel useful for testing and debuging. All notifications are send to OctoberCMS log system.'
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
	    $data = $message->getData();

	    // Send notification to log
	    Log::info('Send a dummny notification', $data);
	}

    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\Notifications\Channels\Listenable::read()
     */
	public function read()
	{
	    
	    $message = new IncomingMessage($this->getKey());
	    $message->from('Julio');
	    $message->setContent(' DMA    345345.34543  ');
	    return [$message];
	    
	}
}
