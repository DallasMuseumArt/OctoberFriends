<?php namespace DMA\Friends;

use Backend;
use Illuminate\Support\Facades\Event;
use Rainlab\User\Models\User as User;
use System\Classes\PluginBase;

/**
 * Friends Plugin Information File
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
            'name'        => 'Friends',
            'description' => 'A platform for users to earn badges and redeem rewards',
            'author'      => 'Dallas Museum of Art',
            'icon'        => 'icon-leaf'
        ];
    }

    public function registerPermissions()
    {
        return [
            'dma.friends.access_admin'  => ['label' => 'Manage Friends'],
        ];
    }

    public function registerNavigation()
    {
        return [
            'friends' => [
                'label'         => 'Friends',
                'url'           => Backend::url('dma/friends/badges'),
                'icon'          => 'icon-users',
                'permissions'   => ['dma.friends.*'],
                'order'         => 500,
                'sideMenu'  => [
                    'badges'    => [
                        'label'     => 'Badges',
                        'icon'      => 'icon-shield',
                        'url'       => Backend::url('dma/friends/badges'),
                        'permissions'   => ['dma.friends.access_admin'],
                    ],
                    'rewards'   => [
                        'label'     => 'Rewards',
                        'icon'      => 'icon-money',
                        'url'       => Backend::url('dma/friends/rewards'),
                    ],
                    'activities'   => [
                        'label'     => 'Activities',
                        'icon'      => 'icon-child',
                        'url'       => Backend::url('dma/friends/activities'),
                    ],  
                    'activitylogs'   => [
                        'label'     => 'Activity Logs',
                        'icon'      => 'icon-rocket',
                        'url'       => Backend::url('dma/friends/activitylogs'),
                    ]
                    
                ]
            ]
        ];
    }

    public function boot()
    {
        // Extend the user model to support our custom metadata
        User::extend(function($model) {
            $model->hasOne['metadata'] = ['DMA\Friends\Models\Usermeta'];
        });

        Event::listen('backend.form.extendFields', function($widget) {
            if (!$widget->getController() instanceof \RainLab\User\Controllers\Users) return;
            if ($widget->getContext() != 'update') return;

            $widget->addFields([
                'metadata[first_name]' => [
                    'label' => 'First Name',
                    'tab'   => 'Metadata',
                ],  
                'metadata[last_name]' => [
                    'label' => 'Last Name',
                    'tab'   => 'Metadata',
                ], 
                'metadata[points]' => [
                    'label' => 'Points',
                    'tab'   => 'Metadata',
                ],
                'metadata[email_optin]' => [
                    'label' => 'Email Opt-in',
                    'type'  => 'checkbox',
                    'tab'   => 'Metadata',
                ],
                'metadata[current_member]' => [
                    'label' => 'Current member?',
                    'type'  => 'checkbox',
                    'tab'   => 'Metadata',
                ],
                'metadata[current_member_number]' => [
                    'label' => 'Current Member Number',
                    'tab'   => 'Metadata',
                ],
            ], 'primary');
        }); 
    }

    public function register()
    {
        $this->registerConsoleCommand('friends.sync-friends-data', 'DMA\Friends\Console\SyncFriendsDataCommand');
    } 

}
