<?php namespace DMA\Friends\Classes\Notifications\Channels;

use DMA\Friends\Classes\Notifications\Channels\Channel;
use DMA\Friends\Classes\Notifications\NotificationMessage;
use DMA\Friends\Models\Notification;

class ChannelKiosk implements Channel
{

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::getKey()
	 */
    public function getKey()
	{
		return 'kiosk';
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::getDetails()
	 */
	public function getDetails()
	{
	    return [
	            'name'           => 'Kiosk',
	            'description'    => 'Store notification in the database. So they can be read in a Kiosk or a Web interface.'
	    ];
	}	
	
	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::settingFields()
	 */
	public function settingFields()
	{
		return [
            'kiosk_notification_max_age' => [
                'label' => 'Maximum days to keep an notifications open',
                'type'  => 'Number',
                'span'  => 'auto',
                'default' => 60
 		    ],
		];
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::configChannel()
	 */
	public function configChannel()
	{
        //  Nothing to do here for now.
	}

	/**
	 * {@inheritDoc}
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::send()
	 */
	public function send(NotificationMessage $message)
	{
        $notification = new Notification();

        $notification->user = $message->getTo();
        $notification->subject = $message->getSubject();
        $notification->message = (string)$message->getContent();
        
        if(!empty($notification->message)){
         
            // Attach object to notification if it exists in the message
            if(!is_null($attachObject = $message->getAttachObject())){
                if(is_object($attachObject)){
                    $notification->object_id   = $attachObject->id;
                    $notification->object_type  = get_class($attachObject);
                }
            }
    
            return $notification->save();
        }
	}
}
