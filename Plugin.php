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
use DB;
use Log;
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

    /**
     * {@inheritDoc}
     */
    public function registerPermissions()
    {
        return [
            'dma.friends.access_admin'  => ['label' => 'Manage Friends'],
        ];
    }

    /**
     * {@inheritDoc}
     */
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
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function registerNavigation()
    {
        return [
            'friends' => [
                'label'         => 'Friends Content',
                'url'           => Backend::url('dma/friends/activities'),
                'icon'          => 'icon-users',
                'permissions'   => ['dma.friends.*'],
                'order'         => 500,
                'sideMenu'  => [
                    'activities'   => [
                        'label'         => 'Activities',
                        'icon'          => 'icon-child',
                        'url'           => Backend::url('dma/friends/activities'),
                        'permissions'   => ['dma.friends.access_admin'],
                    ],  
                    'badges'    => [
                        'label'         => 'Badges',
                        'icon'          => 'icon-shield',
                        'url'           => Backend::url('dma/friends/badges'),
                        'permissions'   => ['dma.friends.access_admin'],
                    ],
                    'rewards'   => [
                        'label'         => 'Rewards',
                        'icon'          => 'icon-money',
                        'url'           => Backend::url('dma/friends/rewards'),
                        'permissions'   => ['dma.friends.access_admin'],
                    ],
                    'categories' => [
                        'label'         => 'Categories',
                        'icon'          => 'icon-list-ul',
                        'url'           => Backend::url('dma/friends/categories'),
                        'permissions'   => ['dma.friends.access_admin'],
                    ],  
                    'activitylogs'   => [
                        'label'         => 'Activity Logs',
                        'icon'          => 'icon-rocket',
                        'url'           => Backend::url('dma/friends/activitylogs'),
                        'permissions'   => ['dma.friends.access_admin'],
                    ],
                    'groups'   => [
                        'label'         => 'Groups',
                        'icon'          => 'icon-users',
                        'url'           => Backend::url('dma/friends/groups'),
                        'permissions'   => ['dma.friends.access_admin'],
                    ],
                    'locations' => [
                        'label'         => 'Locations',
                        'icon'          => 'icon-location-arrow',
                        'url'           => Backend::url('dma/friends/locations'),
                        'permissions'   => ['dma.friends.access_admin'],
                    ],                
                ]
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function registerComponents()
    {
        return [
            'DMA\Friends\Components\ActivityCatalog'            => 'ActivityCatalog',
            'DMA\Friends\Components\ActivityCodeForm'           => 'ActivityCodeForm',
            'DMA\Friends\Components\ActivityFilters'            => 'ActivityFilters',
            'DMA\Friends\Components\ActivityStream'             => 'ActivityStream',
            'DMA\Friends\Components\GetRewards'                 => 'GetRewards',
            'DMA\Friends\Components\Modal'                      => 'Modal',
            'DMA\Friends\Components\Leaderboard'                => 'Leaderboard',
            'DMA\Friends\Components\UserBadges'                 => 'UserBadges',
            'DMA\Friends\Components\UserMostRecentBadge'        => 'UserMostRecentBadge',
            'DMA\Friends\Components\NotificationCounter'        => 'NotificationCounter',
            'DMA\Friends\Components\NotificationList'           => 'NotificationList',
            'DMA\Friends\Components\GroupManager'         		=> 'GroupManager',               
            'DMA\Friends\Components\GroupRequest'       		=> 'GroupRequest',
            'DMA\Friends\Components\GroupJoinCodeForm'          => 'GroupJoinCodeForm',
            'DMA\Friends\Components\UserProfile'                => 'UserProfile',
            'DMA\Friends\Components\UserLogin'                  => 'UserLogin',
            'DMA\Friends\Components\UserTimeout'                => 'UserTimeout',                  
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
            'DMA\Friends\Activities\Registration'   => 'Registration',
        ];
    }

    
    /**
     * {@inheritDoc}
     */
    public function boot()
    {
        
        // Handle locations upon login
        $this->registerLocation();

        // Register timezone settings
        date_default_timezone_set( Settings::get('timezone', Config::get('app.timezone')) );

        // Register ServiceProviders
        App::register('DMA\Friends\FriendsServiceProvider');
        App::register('Maatwebsite\Excel\ExcelServiceProvider');

        // Register aliases
        $alias = AliasLoader::getInstance();
        $alias->alias('Excel', 'Maatwebsite\Excel\Facades\Excel');
        
        // Register Event Subscribers
        $subscriber = new FriendsEventHandler;
        Event::subscribe($subscriber);

        // Generate barcode_id when a user object is created
        // TODO: Migrate when user plugin is forked
        User::creating(function($user)
        {
            if (empty($user->barcode_id)) {
                $user->barcode_id = substr(md5($user->email), 0, 9); 
            }
        });
        
        // Extend the user model to support our custom metadata        
        User::extend(function($model) {        
            $model->hasOne['metadata']          = ['DMA\Friends\Models\Usermeta', 'key' => 'user_id'];     
            $model->hasMany['activityLogs']     = ['DMA\Friends\Models\ActivityLog'];
            $model->hasMany['bookmarks']        = ['DMA\Friends\Models\Bookmark'];
            $model->hasMany['notifications']    = ['DMA\Friends\Models\Notification'];
            $model->belongsToMany['activities'] = ['DMA\Friends\Models\Activity',
                'table' => 'dma_friends_activity_user', 
                'user_id', 
                'activity_id',   
                'timestamps' => true, 
                'order' => 'dma_friends_activity_user.created_at desc'
            ];     
            $model->belongsToMany['steps']      = ['DMA\Friends\Models\Step',
                'table' => 'dma_friends_step_user',     
                'user_id', 
                'step_id',       
                'timestamps' => true, 
                'order' => 'dma_friends_step_user.created_at desc'
            ];     
            $model->belongsToMany['badges']     = ['DMA\Friends\Models\Badge',      
                'table' => 'dma_friends_badge_user',    
                'user_id', 
                'badge_id',      
                'timestamps' => true, 
                'order' => 'dma_friends_badge_user.created_at desc'
            ];        
            $model->belongsToMany['rewards']    = ['DMA\Friends\Models\Reward',     
                'table' => 'dma_friends_reward_user',   
                'user_id', 
                'reward_id',     
                'timestamps' => true, 
                'order' => 'dma_friends_reward_user.created_at desc'
            ];       
            $model->belongsToMany['groups']     = ['DMA\Friends\Models\UserGroup',  
                'table' => 'dma_friends_users_groups',  
                'key' => 'user_id',  
                'foreignKey' => 'group_id', 
                'pivot' => ['membership_status']
            ];        
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
                'barcode_id' => [
                    'label'     => 'Barcode ID',
                ],
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
                'span'  => 'left',
        	],
        	'metadata[last_name]' => [
        		'label' => 'Last Name',
        		'tab'   => 'Metadata',
                'span'  => 'right',
        	],
        	'metadata[email_optin]' => [
        		'label' => 'Email Opt-in',
        		'type'  => 'checkbox',
        		'tab'   => 'Metadata',
        	],
        	'metadata[current_member]' => [
        		'label' => 'Current member?',
        		'type'  => 'dropdown',
                'span'  => 'left',
                'options'   => [
                    'Non Member',
                    'Member',
                    'Staff',
                ],
        		'tab'   => 'Metadata',
        	],
        	'metadata[current_member_number]' => [
        		'label' => 'Current Member Number',
        		'tab'   => 'Metadata',
                'span'  => 'left',
        	],
            'metadata[gender]' => [
                'label' => 'Gender',
                'tab'   => 'Metadata',
                'span'  => 'left',
                //'type'  => 'dropdown',
                //'options'   => '\DMA\Friends\Models\Usermeta::getGenderOptions'
            ],
            'metadata[education]' => [
                'label' => 'Education',
                'tab'   => 'Metadata',
            ],
            'metadata[household_income]' => [
                'label' => 'Household Income',
                'tab'   => 'Metadata',
            ],
            'metadata[household_size]' => [
                'label' => 'Household Size',
                'tab'   => 'Metadata',
            ],
            'points' => [
                'tab'   => 'Points',
                'type'  => 'points',
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
            'rewards[rewards]' => [
                'tab'   => 'Rewards',
                'type'  => 'partial',
                'path'  => '@/plugins/dma/friends/models/reward/users.htm',
            ],
            'print' => [
                'tab'   => 'Membership Card',
                'type'  => 'printmembershipcard',
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
        
    /**
     * {@inheritDoc}
     */
    public function registerFormWidgets()
    {
        return [
            'DMA\Friends\FormWidgets\ActivityType' => [
                'label' => 'ActivityType',
                'code'  => 'activitytype',
            ],
            'DMA\Friends\FormWidgets\TimeRestrictions' => [
                'label' => 'Time Restrictions',
                'code'  => 'timerestrictions',
            ],
            'DMA\Friends\FormWidgets\UserPoints' => [
                'label' => 'Points',
                'code'  => 'points',
            ],
            'DMA\Friends\FormWidgets\PrintMembershipCard' => [
                'label' => 'Print Membership Card',
                'code'  => 'printmembershipcard',
            ],
        ];   
    }

    /**
     * {@inheritDoc}
     */
    public function registerSchedule($schedule)
    {

        $schedule->command("friends.points-weekly")->weekly();
        $schedule->command("friends.points-daily")->daily();
        $schedule->command("friends.read-channels")->everyFiveMinutes();
        $schedule->command("friends.reset-groups")->everyFiveMinutes();

    }

    /**
     * {@inheritDoc}
     */
    public function register()
    {
        $db = false;

        try {
            $db = DB::connection('friends_wordpress');
        } catch (\InvalidArgumentException $e) {
            //Log::info('Missing configuration for wordpress migration');
        }

        if ($db) {
            // Commands for syncing wordpress data
            $this->registerConsoleCommand('friends.sync-data', 'DMA\Friends\Commands\SyncFriendsDataCommand');
            $this->registerConsoleCommand('friends.sync-relations', 'DMA\Friends\Commands\SyncFriendsRelationsCommand');
            $this->registerConsoleCommand('friends.sync-images', 'DMA\Friends\Commands\SyncFriendsImagesCommand');
        }

        $this->registerConsoleCommand('friends.normalize-users', 'DMA\Friends\Commands\NormalizeUserData');
        $this->registerConsoleCommand('friends.points-weekly', 'DMA\Friends\Commands\WeeklyPoints');
        $this->registerConsoleCommand('friends.points-daily', 'DMA\Friends\Commands\DailyPoints');
        $this->registerConsoleCommand('friends.read-channels', 'DMA\Friends\Commands\ReadChannels');
        $this->registerConsoleCommand('friends.reset-groups', 'DMA\Friends\Commands\ResetGroups');

    } 
    
    /**
     * Register Friends API resource endpoints 
     * 
     * @return array
     */
    public function registerFriendAPIResources()
    {
        return [
            'activities'            => '\DMA\Friends\API\Resources\ActivityResource',
            'activity-logs'         => '\DMA\Friends\API\Resources\ActivityLogResource',
            'activity-metadata'     => '\DMA\Friends\API\Resources\ActivityMetadataResource',
            'badges'                => '\DMA\Friends\API\Resources\BadgeResource',
            'steps'                 => '\DMA\Friends\API\Resources\StepResource',
            'categories'            => '\DMA\Friends\API\Resources\CategoryResource',
            'locations'             => '\DMA\Friends\API\Resources\LocationResource',
            'rewards'               => '\DMA\Friends\API\Resources\RewardResource',
            'users'                 => '\DMA\Friends\API\Resources\UserResource',
            'countries'             => '\DMA\Friends\API\Resources\CountryResource',   
            'countries.states'      => '\DMA\Friends\API\Resources\StateResource',
        ];
    }
    

    /**
     * {@inheritDoc}
     */
    public function registerReportWidgets()
    {   
        return [
            'DMA\Friends\ReportWidgets\FriendsToolbar' => [
                'label'     => 'Friends Toolbar',
                'context'   => 'dashboard'
            ],
            // 'DMA\Friends\ReportWidgets\DatePicker' => [
            //     'label'     => 'Friends Date Picker',
            //     'context'   => 'dashboard',
            // ],
            'DMA\Friends\ReportWidgets\FriendsLeaderboard' => [
                'label'     => 'Table - Friends Leaderboard',
                'context'   => 'dashboard',
            ],
            'DMA\Friends\ReportWidgets\UserReport' => [
                'label'     => 'Graph - # Users by day',
                'context'   => 'dashboard',
            ],
            'DMA\Friends\ReportWidgets\RewardReport' => [
                'label'     => 'Graph - # Rewards redeemed by day',
                'context'   => 'dashboard',
            ],
            'DMA\Friends\ReportWidgets\TopRewards' => [
                'label'     => 'Table - Top Rewards',
                'context'   => 'dashboard',
            ],
            'DMA\Friends\ReportWidgets\TopActivities' => [
                'label'     => 'Table - Top Activities',
                'context'   => 'dashboard',
            ],
            'DMA\Friends\ReportWidgets\ActivitiesByDay' => [
                'label'     => 'Graph - Activities By Day',
                'context'   => 'dashboard',
            ],
            'DMA\Friends\ReportWidgets\EmailOptin' => [
                'label'     => 'Chart - % Users with email optin',
                'context'   => 'dashboard',
            ],
            'DMA\Friends\ReportWidgets\FriendsMembers'=>[
                'label'   => 'Chart - % of Users with partnership',
                'context' => 'dashboard'
            ],
        ];  
    } 
   
    /**
     * {@inheritDoc}
     */ 
    public function registerMarkupTags()
    {   
        return [
            'tokens' => [
                 'flashMessages' =>  new \DMA\Friends\Classes\Notifications\Twig\FlashMultipleTokenParser()
             ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function registerMailTemplates()
    {
        return [
            'dma.friends::mail.invite'      => 'Invitation email to join a group sent when a user is added to a group.',
            'dma.friends::mail.badge'       => 'Email to send to a user when a badge is awarded',
            'dma.friends::mail.reward'      => 'Email to send to a user when a reward is redeemed',
            'dma.friends::mail.adminReward' => 'Administrative email to send when a reward is redeemed',
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

