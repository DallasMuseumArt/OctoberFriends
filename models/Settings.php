<?php namespace DMA\Friends\Models;

use Model;

/**
 * Settings Model
 */
class Settings extends Model
{

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
}