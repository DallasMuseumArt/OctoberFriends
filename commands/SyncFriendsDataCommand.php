<?php

namespace DMA\Friends\Commands;

use Log;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use DMA\Friends\Wordpress\Activity as WordpressActivity;
use DMA\Friends\Wordpress\ActivityLog as WordpressActivityLog;
use DMA\Friends\Wordpress\Badge as WordpressBadge;
use DMA\Friends\Wordpress\Location as WordpressLocation;
use DMA\Friends\Wordpress\Reward as WordpressReward;
use DMA\Friends\Wordpress\Step as WordpressStep;
use DMA\Friends\Wordpress\Taxonomy as WordpressTaxonomy;
use DMA\Friends\Wordpress\User as WordpressUser;

/**
 * Syncronize the data from wordpress into october
 *
 * @package DMA\Classes\Commands
 * @author Kristen Arnold, Carlos Arroyo
 */
class SyncFriendsDataCommand extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'friends:sync-data';

    /**
     * @var string The console command description.
     */
    protected $description = 'Syncronize wordpress data into October';

    /**
     * @var object Contains the database object when fired
     */
    protected $db = null;

    /**
     * @var Number of records to process per run
     */
    protected $limit = 1000;

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {
        try {
            $this->db = DB::connection('friends_wordpress');
        } catch (\InvalidArgumentException $e) {
            Log::info('Missing configuration for wordpress migration');
        }

        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function fire()
    {
        $this->info('Initializing Syncronization');

        $type = $this->option('type');
        $this->limit = $this->option('limit');

        switch($type) {
            case 'users':
                $this->syncUsers();
                break;
            case 'activities':
                $this->syncActivities();
                break;
            case 'activity-logs':
                $this->syncActivityLogs();
                break;
            case 'badges':
                $this->syncBadges();
                break;
            case 'locations':
                $this->syncLocations();
                break;
            case 'rewards':
                $this->syncRewards();
                break;
            case 'steps':
                $this->syncSteps();
                break;
            case 'taxonomy':
                $this->syncTaxonomy();
                break;
            default:
                $this->syncUsers();
                $this->syncActivities();
                $this->syncBadges();
                $this->syncLocations();
                $this->syncRewards();
                $this->syncSteps();
                $this->syncTaxonomy();
                $this->syncActivityLogs();
        }

        $this->info('Sync complete');
    }

    /**
     * Syncronize wordpress user accounts with laravel
     */
    protected function syncUsers()
    {
        $user = new WordpressUser;

        if (!$this->option('exclude-meta-update')) {
            $this->info('Updating existing users metadata');
            $user->updateExistingUsers();
        }

        $this->sync($user, 'users');
    }

    /**
     * Syncronize wordpress activities with laravel
     * @return int
     */
    protected function syncActivities()
    {
        $activities = new WordpressActivity;
        $this->sync($activities, 'activities');
    }

    /**
     * Syncronize BadgeOS Activity Logs with laravel
     * @return int
     */
    protected function syncActivityLogs()
    {
        $activityLogs = new WordpressActivityLog;
        $this->sync($activityLogs, 'activity logs');

    }

    /**
     * Syncronize wordpress badges with laravel
     * @return int
     */
    protected function syncBadges()
    {
        $badges = new WordpressBadge;
        $this->sync($badges, 'badges');
    }

    /** 
     * Syncronize wordpress locations with laravel
     * @return int
     */
    protected function syncLocations()
    {   
        $locations = new WordpressLocation;
        $this->sync($locations, 'locations');
    }  

    /** 
     * Syncronize wordpress rewards with laravel
     * @return int
     */
    protected function syncRewards()
    {   
        $rewards = new WordpressReward;
        $this->sync($rewards, 'rewards');
    }  

    /** 
     * Syncronize wordpress steps with laravel
     * @return int
     */
    protected function syncSteps()
    {   
        $steps = new WordpressStep;
        $this->sync($steps, 'steps');
    }

    /**
     * Syncronize wordpress taxonomy terms with laravel
     */
    protected function syncTaxonomy()
    {
        $taxonomy = new WordpressTaxonomy;
        $this->sync($taxonomy, 'taxonomy');
    }

    protected function sync($model, $textType)
    {
        $this->info('Begin sync of ' . $textType);
        $count = $model->import($this->limit);
        $this->info('Processed ' . $count . ' ' . $textType);
    }

    /** 
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {   
        return [
            ['type', null, InputOption::VALUE_OPTIONAL, 'Import specific type', null],
            ['limit', null, InputOption::VALUE_OPTIONAL, 'Number of records per type to import', $this->limit],
            ['exclude-meta-update', null, InputOption::VALUE_NONE, 'Exclude update of user metadata', null],
        ];  
    }  
}
