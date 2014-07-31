<?php namespace Dma\Friends;

use System\Classes\PluginBase;

/**
 * friends Plugin Information File
 */
class Plugin extends PluginBase
{

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'          => 'DMA Friends',
            'description'   => 'A platform for providing badges and rewards to institutional constituents',
            'author'        => 'Dallas Museum of Art',
            'icon'          => 'icon-users',
        ];  
    }   

    public function registerComponents()
    {
        return [
            'DMA\Friends\Components\Badge'      => 'friendsBadge',
            //'DMA\Friends\Components\Activity'   => 'friendsActivity',
            //'DMA\Friends\Components\Reward'     => 'friendsReward',
        ];  
    }  

}
