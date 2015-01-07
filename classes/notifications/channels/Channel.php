<?php namespace DMA\Friends\Classes\Notifications\Channels;

use DMA\Friends\Classes\Notifications\NotificationMessage;

interface Channel
{

    /**
     * Return details of the channel. 
     * Manly used in the Backend interface.
     *
     * @return array
     * 
     * eg.
     * [
     *  	'name' => 'Kiosk',
     *  	'description' => 'Store notification in the database. So they can be read in a Kiosk or a Web interface.'
     * ]
     */
    public function getDetails();
    
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
