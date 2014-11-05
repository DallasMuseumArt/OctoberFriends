<?php namespace DMA\Friends\Models;

use Model;
use Postman;

/**
 * Friends Settings model
 * @package DMA\Friends\Models
 * @author Kristen Arnold, Carlos Arroyo
 *
 */
class Settings extends Model{
    
    public $implement = ['System.Behaviors.SettingsModel'];
    
    // A unique code
    public $settingsCode = 'dma_friends_settings';
    
    // Reference to field configuration
    public $settingsFields = 'fields.yaml';    
    
        
    /**
     * Returns available timezones
     * @return array
     */
    public function getTimezoneOptions()
    {
        $timezones = timezone_identifiers_list();
        $timezones = array_combine($timezones, $timezones);

        return $timezones;
    }

    /**
     * Return al available channels in the platform
     * 
     * @param boolean $onlyListenable
     * Only return channels that implement Listenable interface
     *  
     * @param boolean $description   
     * Include channel description 
     * 
     * @return array
     */
    private function getChannelOptions($onlyListenable=false, $description=true){
    	$options = [];
    	foreach(Postman::getRegisterChannels($onlyListenable) as $ch){
    		$info = $ch->info;
    		$options[$ch->getKey()] = [@$info['name'], ($description) ? @$info['description'] : ''];
    	}
    	return $options;
    }
    
    
    /**
     * Return Active channels for notification.
     * 
     * @return array
     */
    public function getActiveNotificationChannelsOptions(){
    	return $this->getChannelOptions();
    }
    
    /**
     * Return Active channels that can trigger actions in 
     * the platform.
     * 
     * @return array
     */
    public function getActiveListenableChannelsOptions(){
    	return $this->getChannelOptions(true, $description=false); 
    }   
}