<?php namespace DMA\Friends;

use Backend;
use Illuminate\Support\Facades\Event;
use Rainlab\User\Models\User as User;
use DMA\Friends\Models\Usermeta as Metadata;
use DMA\Friends\Models\Settings;
use DMA\Friends\Classes\LocationManager;
use DMA\Friends\Classes\ActivityManager;
use System\Classes\PluginBase;
use DMA\Friends\Classes\FriendsEventHandler;
use App;
use Config;
use Illuminate\Foundation\AliasLoader;

/**
 * Friends Plugin Information File
 *
 * @package DMA\Friends
 * @author Kristen Arnold, Carlos Arroyo
 */
class Plugin extends PluginBase
{

    /** 
     * @var array Plugin dependencies
     */
    public $require = [
        'RainLab.User'
    ]; 

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

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Friends Settings',
                'description' => 'Manage user based settings.',
                'category'    => 'Friends',
                'icon'        => 'icon-cog',
                'class'       => 'DMA\Friends\Models\Settings',
                'order'       => 500,
                'keywords'    => 'friends system settings'
            ],
            'locations' => [
                'label'         => 'Locations',
                'description'   => 'Manage the kiosk locations',
                'category'      => 'Friends',
                'icon'          => 'icon-location-arrow',
                'url'           => Backend::url('dma/friends/locations'),
                'order'         => 0,
            ],  
            'categories' => [
                'label'         => 'Categories',
                'description'   => 'Manage the Categories',
                'category'      => 'Friends',
                'icon'          => 'icon-square',
                'url'           => Backend::url('dma/friends/categories'),
                'order'         => 10,
            ],
        ];
    }

    public function registerNavigation()
    {
        return [
            'friends' => [
                'label'         => 'Friends',
                'url'           => Backend::url('dma/friends/activities'),
                'icon'          => 'icon-users',
                'permissions'   => ['dma.friends.*'],
                'order'         => 500,
                'sideMenu'  => [
                    'activities'   => [
                        'label'     => 'Activities',
                        'icon'      => 'icon-child',
                        'url'       => Backend::url('dma/friends/activities'),
                    ],  
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
                    'activitylogs'   => [
                        'label'     => 'Activity Logs',
                        'icon'      => 'icon-rocket',
                        'url'       => Backend::url('dma/friends/activitylogs'),
                    ],
                    'groups'   => [
                        'label'     => 'Friends Groups',
                        'icon'      => 'icon-users',
                        'url'       => Backend::url('dma/friends/groups'),
                    ],                    
                    
                ]
            ]
        ];
    }

    public function registerComponents()
    {
        return [
            'DMA\Friends\Components\ActivityCodeForm'           => 'ActivityCodeForm',
            'DMA\Friends\Components\ActivityStream'             => 'ActivityStream',
            'DMA\Friends\Components\BadgeRecommend'             => 'BadgeRecommend',
            'DMA\Friends\Components\GetRewards'                 => 'GetRewards',
            'DMA\Friends\Components\Modal'                      => 'Modal',
            'DMA\Friends\Components\UserBadges'                 => 'UserBadges',
            'DMA\Friends\Components\UserMostRecentBadge'        => 'UserMostRecentBadge',
            'DMA\Friends\Components\NotificationCounter'        => 'NotificationCounter',
            'DMA\Friends\Components\NotificationList'           => 'NotificationList',
            'DMA\Friends\Components\GroupFormCreation'  		=> 'GroupFormCreation',
            'DMA\Friends\Components\GroupRequest'       		=> 'GroupRequest',                        
        ];
    }

    /**
     * Register additional friends activity types
     */
    public function registerFriendsActivities()
    {
        return [
            'DMA\Friends\Activities\ActivityCode'   => 'ActivityCode',
            'DMA\Friends\Activities\LikeWorkOfArt'  => 'LikeWorkOfArt',
        ];
    }

    public function boot()
    {

        // Handle locations upon login
        $this->registerLocation();

        // Register timezone settings
        date_default_timezone_set( Settings::get('timezone', Config::get('app.timezone')) );

        // Register ServiceProviders
        App::register('\EllipseSynergie\ApiResponse\Laravel\ResponseServiceProvider');
        App::register('DMA\Friends\FriendsServiceProvider');
        
        // Register Event Subscribers
        $subscriber = new FriendsEventHandler;
        Event::subscribe($subscriber);

        // Extend the user model to support our custom metadata        
        User::extend(function($model) {        
            $model->hasOne['metadata']          = ['DMA\Friends\Models\Usermeta'];     
            $model->hasMany['activityLogs']     = ['DMA\Friends\Models\ActivityLog'];
            $model->hasMany['notifications']    = ['DMA\Friends\Models\Notification'];
            $model->belongsToMany['activities'] = ['DMA\Friends\Models\Activity',   'table' => 'dma_friends_activity_user', 'user_id', 'activity_id',   'timestamps' => true, 'order' => 'dma_friends_activity_user.created_at desc'];     
            $model->belongsToMany['steps']      = ['DMA\Friends\Models\Step',       'table' => 'dma_friends_step_user',     'user_id', 'step_id',       'timestamps' => true, 'order' => 'dma_friends_step_user.created_at desc'];     
            $model->belongsToMany['badges']     = ['DMA\Friends\Models\Badge',      'table' => 'dma_friends_badge_user',    'user_id', 'badge_id',      'timestamps' => true, 'order' => 'dma_friends_badge_user.created_at desc'];        
            $model->belongsToMany['rewards']    = ['DMA\Friends\Models\Reward',     'table' => 'dma_friends_reward_user',   'user_id', 'reward_id',     'timestamps' => true, 'order' => 'dma_friends_reward_user.created_at desc'];       
            $model->belongsToMany['groups']     = ['DMA\Friends\Models\UserGroup',  'table' => 'dma_friends_users_groups',  'primaryKey' => 'user_id',  'foreignKey' => 'group_id', 'pivot' => ['membership_status']];        
        });
        
        // Extend User fields
        $context = $this;
        Event::listen('backend.form.extendFields', function($widget) use ($context){
            $context->extendedUserFields($widget);
            $context->extendedSettingFields($widget);
        }); 

        Event::listen('backend.list.extendColumns', function($widget) {
            if (!$widget->getController() instanceof \RainLab\User\Controllers\Users) return;

            $widget->addColumns([
                'full_name' => [
                    'label'         => 'Full Name',
                    'relation'      => 'metadata',
                    'sortable'   => false,
                    'select'        => "concat(first_name, ' ', last_name)", 
                    'searchable'    => true,
                ],
                'first_name' => [
                    'label'         => 'First Name',
                    'relation'      => 'metadata',
                    'select'        => '@first_name',
                    'searchable'    => true,
                ],  
                'last_name' => [
                    'label'         => 'Last Name',
                    'relation'      => 'metadata',
                    'select'        => '@last_name',
                    'searchable'    => true,
                ], 
                'points' => [
                    'label'     => 'Points'
                ], 
                'zip' => [
                    'label' => 'Zip',
                ],            
            ]); 
        });

    }
    
    /**
     * Extend User fields in Rainlab.User plugin
     * @param mixed $widget
     */
    private function extendedUserFields($widget)
    {
        if (!$widget->getController() instanceof \RainLab\User\Controllers\Users) return;
        if ($widget->getContext() != 'update') return;
        
        // Make sure the User metadata exists for this user.
        if (!Metadata::getFromUser($widget->model)) return;
        
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
            'activities[activities]' => [
                'tab'   => 'Activities',
                'type'  => 'partial',
                'path'  => '@/plugins/dma/friends/models/activity/users.htm',
            ],
            'badges[badges]' => [
                'tab'   => 'Badges',
                'type'  => 'partial',
                'path'  => '@/plugins/dma/friends/models/badge/users.htm',
            ],
        ], 'primary');        
    }
    
    /**
     * Add settings fields of all available channels.
     * @param mixed $form
     */
    private function extendedSettingFields($form)
    {
        if (!$form->model instanceof \DMA\Friends\Models\Settings) return;
        if ($form->getContext() != 'update') return;
        
        $form->addFields(\Postman::getChannelSettingFields(), 'primary');        
    }
    
    
    public function registerFormWidgets()
    {
        return [
            'DMA\Friends\FormWidgets\ActivityType' => [
                'label' => 'ActivityType',
                'alias' => 'activitytype',
            ],
            'DMA\Friends\FormWidgets\TimeRestrictions' => [
                'label' => 'Time Restrictions',
                'alias' => 'timerestrictions',
            ],
        ];   
    }

    public function register()
    {
        // Commands for syncing wordpress data
        $this->registerConsoleCommand('friends.sync-data', 'DMA\Friends\Commands\SyncFriendsDataCommand');
        $this->registerConsoleCommand('friends.sync-relations', 'DMA\Friends\Commands\SyncFriendsRelationsCommand');

        // Crontasks
        $this->registerConsoleCommand('friends.points-weekly', 'DMA\Friends\Commands\WeeklyPoints');
        $this->registerConsoleCommand('friends.points-daily', 'DMA\Friends\Commands\DailyPoints');

    } 

    public function registerReportWidgets()
    {   
        return [
            'DMA\Friends\ReportWidgets\FriendsToolbar' => [
                'label'     => 'Friends Toolbar',
                'context'   => 'dashboard'
            ],  
            'DMA\Friends\ReportWidgets\FriendsLeaderboard' => [
                'label'     => 'Friends Leaderboard',
                'context'   => 'dashboard',
            ],
        ];  
    } 
    
    public function registerMailTemplates()
    {
        return [
                'dma.friends::mail.invite' => 'Invitation email to join a group sent when a user is added to a group.',
        ];
    }    

    /**
     * register the location of a kiosk with the browser session
     */
    public function registerLocation()
    {
        if (!isset($_SERVER['HTTP_X_DEVICE_UUID'])) return;

        $uuid = $_SERVER['HTTP_X_DEVICE_UUID'];

        $manager = new LocationManager($uuid);
    }

}

