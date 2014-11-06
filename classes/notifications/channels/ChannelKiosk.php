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
	 * @see \DMA\Friends\Classes\Notifications\Channels\Channel::settingFields()
	 */
	public function settingFields()
	{
		return [
            'kiosk_mnotification_max_age' => [
                'label' => 'Maximum days to keep an notificaion open',
                'type'  => 'Number',
                'span'  => 'auto',
                'default' => 120
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
        $notification->message = $message->getContent();

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
