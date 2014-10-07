<?php namespace DMA\Friends\Models;

use Model;

/**
 * Friends Settings model
 * @package DMA\Friends\Models
 * @author Carlos Arroyo
 *
 */
class Settings extends Model{
	
	public $implement = ['System.Behaviors.SettingsModel'];
	
	public $settingsCode = 'friends_settings';
	public $settingsFields = 'fields.yaml';	
	
	const CHANNEL_TEXT  = 'text';
	const CHANNEL_EMAIL = 'email';
	const CHANNEL_KIOSK = 'kiosk';
	
	/**
	 * Default values to set for this model, override
	 */
	public function initSettingsData(){
		$this->maximum_users_group = 5;
		$this->comunication_channel = self::CHANNEL_EMAIL;
		$this->group_email_invite_template = 'dma.friends::mail.group.invite';
	}		
		

	public function getComunicationChannelOptions(){
		return [
			self::CHANNEL_EMAIL => ['Email', 'TODO : Morbi tincidunt lorem sit amet.'],		
			self::CHANNEL_TEXT  => ['Text', 'TODO : Morbi tincidunt lorem sit amet.'],
			self::CHANNEL_KIOSK => ['Kiosk', 'TODO : Morbi tincidunt lorem sit amet.'],
		];
	}
	
}