<?php

namespace DMA\Friends\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Schema;
use Rainlab\User\Models\User;
use DMA\Friends\Wordpress\Post;
use DMA\Friends\Models\Activity;
use DMA\Friends\Models\Badge;
use DMA\Friends\Models\Step;
use DMA\Friends\Models\Location;

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
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {   
        $this->db = DB::connection('friends_wordpress');

        parent::__construct();
    }   

    /** 
     * Execute the console command.
     * @return void
     */
    public function fire()
    {  

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
                    case 'dma_friends_step_badge':
                        $to->steps()->save($from);
                        echo 'from: ' . $from->title . ' --|-|-- ' . $to->title ."\n";

                    default:

                        $values = [
                            $from_table . '_id'   => $from->id,
                            $to_table . '_id'     => $to->id
                        ];

                        if (Schema::hasTable($table)) {
                            DB::table($table)->insert($values);
                            echo 'from: ' . $from->title . ' ----- ' . $to->title ."\n";
                        } else {
                            echo 'table doesnt exist';
                        } 

                }
            }
        }

        // User achievements
        $achievements = $this->db->table('wp_usermeta')
            ->where('meta_key', '_badgeos_achievements')
            ->get();

        $post = new Post;

        foreach ($achievements as $achievement) {
            $user = User::find($achievement->user_id);

            if (!$user) continue;
            
            // Flush existing records
            DB::table('dma_friends_user_steps')
                ->where('user_id', $user->id)
                ->delete();

            DB::table('dma_friends_badge_user')
                ->where('user_id', $user->id)
                ->delete();

            $data = unserialize($achievement->meta_value);

            foreach($data as $d) {
                // wtf we don't need arrays in our arrays if we want to array
                $d = array_pop($d);

                $link = [
                    'user_id'       => $user->id,
                    'created_at'    => $post->epochToTimestamp($d->date_earned),
                ];

                // About half way thru the data the location key changes.
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
                    $link['step_id'] = $step->id;
                    DB::table('dma_friends_user_steps')->insert($link);

                } elseif ($d->post_type == 'badge') {

                    $badge = Badge::findWordpress($d->ID)->first();
                    $link['badge_id'] = $badge->id;
                    DB::table('dma_friends_badge_user')->insert($link);

                }
        
            }
        }
    }
}
