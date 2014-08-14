<?php

namespace DMA\Friends\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Rainlab\User\Models\User;
use DMA\Friends\Wordpress\Post;
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
        $achievements = $this->db->table('wp_usermeta')
            ->where('meta_key', '_badgeos_achievements')
            ->get();

        foreach ($achievements as $achievement) {
            $post = new Post;
            $user = User::find($achievement->user_id);

            if (!$user) continue;
            
            // Flush existing records
            DB::table('dma_friends_user_steps')
                ->where('user_id', $user->id)
                ->delete();

            DB::table('dma_friends_user_badges')
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
                    DB::table('dma_friends_user_badges')->insert($link);

                }
        
            }
        }
    }
}
