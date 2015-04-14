<?php

namespace DMA\Friends\Commands;

use Schema;
use Log;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Rainlab\User\Models\User;
use DMA\Friends\Wordpress\Post;
use DMA\Friends\Models\Activity;
use DMA\Friends\Models\ActivityLog;
use DMA\Friends\Models\Badge;
use DMA\Friends\Models\Category;
use DMA\Friends\Models\Step;
use DMA\Friends\Models\Reward;
use DMA\Friends\Models\Location;

/**
 * Syncronize the relationships between various models from wordpress into october
 *
 * @package DMA\Friends\Commands
 * @author Kristen Arnold, Carlos Arroyo
 */
class SyncFriendsRelationsCommand extends Command
{
    /** 
     * @var string The console command name.
     */
    protected $name = 'friends:sync-relations';

    /** 
     * @var string The console command description.
     */
    protected $description = 'Syncronize wordpress data relations';

    /** 
     * @var object Contains the database object when fired
     */
    protected $db = null;

    /**
     * @var The user/step pivot table
     */
    protected $userStepTable = 'dma_friends_step_user';

    /**
     * @var The user/badge pivot table
     */
    protected $userBadgeTable = 'dma_friends_badge_user';

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

        // Taxonomy terms
        $termRelations = $this->db->table('wp_term_relationships')->get();

        foreach($termRelations as $relation) {
            $activity = Activity::findWordpress($relation->object_id)->first();
            
            if ($activity) {
                if (!$activity->categories->contains($relation->term_taxonomy_id)) {
                    $category = Category::find($relation->term_taxonomy_id);
                    if ($category) {
                        $activity->categories()->save($category);
                    }
                }
            }
        }

        // p2p connections
        $p2ps = $this->db->table('wp_p2p')
            ->get();

        foreach($p2ps as $p2p) {
            list($from, $t, $to) = explode('-', $p2p->p2p_type);

            switch($from) {
                case 'activity':
                    $from = Activity::findWordpress($p2p->p2p_from)->first();
                    $from_table = 'activity';
                    break;
                case 'badge':
                    $from = Badge::findWordpress($p2p->p2p_from)->first();
                    $from_table = 'badge';
                    break;
                case 'dma-location':
                    $from = Location::findWordpress($p2p->p2p_from)->first();
                    $from_table = 'location';
                    break;
                case 'step':
                    $from = Step::findWordpress($p2p->p2p_from)->first();
                    $from_table = 'step';
                    break;
                default:
                    $from = false;
            }

            switch($to) {
                case 'activity':
                    $to = Activity::findWordpress($p2p->p2p_to)->first();
                    $to_table = 'activity';
                    break;
                case 'badge':
                    $to = Badge::findWordpress($p2p->p2p_to)->first();
                    $to_table = 'badge';
                    break;
                case 'dma-location':
                    $to = Location::findWordpress($p2p->p2p_to)->first();
                    $to_table = 'location';
                    break;
                case 'step':
                    $to = Step::findWordpress($p2p->p2p_to)->first();
                    $to_table = 'step'; 
                    break;
                default:
                    $to = false;
            }

            if ($from && $to) {
                $table = 'dma_friends_' . $from_table . '_' . $to_table;

                switch($table) {
                    case 'dma_friends_activity_step':
                        $from->steps()->save($to);
                        $this->info('activity: ' . $from->title . ' --> step: ' . $to->title);
                        break;
                    case 'dma_friends_step_badge':
                        $to->steps()->save($from);
                        $this->info('step: ' . $from->title . ' --> badge: ' . $to->title);
                        break;
                    default:

                        $values = [
                            $from_table . '_id'   => $from->id,
                            $to_table . '_id'     => $to->id
                        ];

                        if (Schema::hasTable($table)) {
                            DB::table($table)->insert($values);
                            $this->info('from: ' . $from->title . ' ----- ' . $to->title);
                        } else {
                            $this->error('table doesnt exist: ' . $table);
                        } 

                }
            }
        }

        // User achievements
        $achievements = $this->db->table('wp_usermeta')
            ->where('meta_key', '_badgeos_achievements')
            ->get();

        $post = new Post;

        $this->info('Sync Achievements');        

        foreach ($achievements as $achievement) {
            $user = User::find($achievement->user_id);

            if (empty($user)) continue;

            // Flush existing records
            DB::table($this->userStepTable)
                ->where('user_id', $user->id)
                ->delete();

            DB::table($this->userBadgeTable)
                ->where('user_id', $user->id)
                ->delete();

            $data = unserialize($achievement->meta_value);

            // wtf we don't need arrays in our arrays if we want to array
            $data = array_pop($data);

            foreach($data as $d) {

                $link = [
                    'user_id'       => $user->id,
                    'created_at'    => $post->epochToTimestamp($d->date_earned),
                ];

                // About half way thru the data for the location key changes.
                // so lets deal with that
                if (isset($d->location)) {
                    $location_id = $d->location;
                } elseif (isset($d->location_earned)) {
                    $location_id = $d->location_earned;
                } else {
                    $location_id = null;
                }

                $location = Location::findWordpress($location_id)->first();
                if (isset($location->id)) {
                    $link['location_id'] = $location->id;
                }

                if ($d->post_type == 'step') {

                    $step = Step::findWordpress($d->ID)->first();
                    if ($step) {
                        $link['step_id'] = $step->id;
                        DB::table($this->userStepTable)->insert($link);
                    }

                } elseif ($d->post_type == 'badge') {

                    $badge = Badge::findWordpress($d->ID)->first();

                    if ($badge) {
                        $link['badge_id'] = $badge->id;
                        DB::table($this->userBadgeTable)->insert($link);
                    }
                }

            }
        }

        // Syncronize activities and users

        $this->info('Importing Activity/User relations');
        
        $table = 'dma_friends_activity_user';

        DB::table($table)->delete();

        ActivityLog::where('action', '=', 'activity')->chunk(100, function($activityLogs) use ($table) {
            foreach ($activityLogs as $activityLog) {

                if ($activityLog->object_id) {

                    echo '.';

                    $pivotTable = [
                        'user_id'       => $activityLog->user_id,
                        'activity_id'   => $activityLog->object_id,
                        'created_at'    => $activityLog->timestamp,
                        'updated_at'    => $activityLog->timestamp,
                    ];

                    DB::table($table)->insert($pivotTable);
                }
            }
        });

        // Syncronize rewards and users

        $this->info('Importing Reward/User relations');
        
        $table = 'dma_friends_reward_user';

        //DB::table($table)->delete();

        ActivityLog::where('action', '=', 'reward')
            ->where('timestamp', '<', '2015-02-02 12:10:35')
            ->chunk(100, function($activityLogs) use ($table) {
            
            foreach ($activityLogs as $activityLog) {

                if ($activityLog->object_id) {

                    echo '.';

                    if (Reward::find($activityLog->object_id) && User::find($activityLog->user_id)) {
                        $pivotTable = [
                            'user_id'       => $activityLog->user_id,
                            'reward_id'     => $activityLog->object_id,
                            'created_at'    => $activityLog->timestamp,
                            'updated_at'    => $activityLog->timestamp,
                        ];

                        DB::table($table)->insert($pivotTable);
                    }
                }
            }
        });

        $this->info('Sync complete');

    }
}
