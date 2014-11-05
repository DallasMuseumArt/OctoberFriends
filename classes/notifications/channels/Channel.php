<?php namespace DMA\Friends\Classes\Notifications\Channels;

use DMA\Friends\Classes\Notifications\NotificationMessage;

interface Channel
{

	/**
	 * Used to identify view per channel.
	 *
	 * @return string
	 */
	public function getKey();


	/**
	 * Load channel configurations.
	 */
	public function configChannel();


	/**
	 * Return settings fields for this channel.
	 * For futher information go to http://octobercms.com/docs/plugin/settings#database-settings
	 *
	 * @return array
	 */
	public function settingFields();

	/**
	 * Send notifiation
	 * @param DMA\Friends\Classes\Notifications\NotificationMessage
	 * @return boolean
	 */
	public function send(NotificationMessage $message);


}
