<?php namespace DMA\Friends\Models;

use Model;
use System\Models\MailTemplate;

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
    
 	// Array of days
    private $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
    
    /**
     * Default values to set for this model, override
     */
    public function initSettingsData()
    {
        $this->maximum_users_group  = 5;
        $this->maximum_points_group = 200;
        //$this->mail_group_invite_template = 'dma.friends::mail.invite'; 
        $this->reset_groups_every_day = $this->days;
        $this->reset_groups_time = '00:00';
    }        
        

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

    public function getMailGroupInviteTemplateOptions()
    {    
        switch ($this->comunication_channel)
        {
            case self::CHANNEL_EMAIL:
                return MailTemplate::where('code', 'LIKE', 'dma.friends::%')
                                    ->orderBy('code')
                                    ->lists('code', 'code');
                break;
            case self::CHANNEL_TEXT:
                return [];//MailTemplate::orderBy('code')->lists('code', 'code');
                break;
            case self::CHANNEL_KIOSK:
                return [];//MailTemplate::orderBy('code')->lists('code', 'code');
                break;                    
        }
        
    }    
    
    public function getResetGroupsEveryDayOptions()
    {
        $opts = [];
        foreach ($this->days as $day){
            $opts[$day] = ucwords($day);
        }
        return $opts;
    }    
}