<?php
namespace DMA\Friends\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use DB;
use DMA\Friends\Models\Reward;
use DMA\Friends\Models\Badge;
use System\Models\File;

/**
 * Set a cron task that will reset the points all users have earned for the week back to zero
 *
 * @package DMA\Friends\Commands
 * @author Kristen Arnold, Carlos Arroyo
 */
class SyncFriendsImagesCommand extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'friends:sync-images';

    /**
     * @var string The console command description.
     */
    protected $description = 'Syncronize wordpress images into October';

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
     * Set the number of points set per week back to zero on sunday morning
     * @return void
     */
    public function fire()
    {

        $this->info('Importing badge images');

        Badge::where('wordpress_id', '>', 0)->chunk(200, function($badges) {
            foreach ($badges as $badge) {
                if (isset($badge->wordpress_id)) {
                    $image = $this->getImage($badge->wordpress_id);

                    if (isset($image->guid)) {
                        $this->processImage($badge, $image->guid);
                    }
                }
            }
        });

        $this->info('Importing reward images');

        Reward::where('wordpress_id', '>', 0)->chunk(200, function($rewards) {
            foreach ($rewards as $reward) {
                if (isset($reward->wordpress_id)) {
                    $image = $this->getImage($reward->wordpress_id);

                    if (isset($image->guid)) {
                        $this->processImage($reward, $image->guid);
                    }
                }
            }
        });

    }

    public function processImage($object, $image) {
        $basename = basename($image);
        $dst = '/tmp/' . $basename;
        copy($image, $dst);
        
        $file = new File;
        $file->data = $dst;
        $file->is_public = true;
        $file->save();

        if ($file) {
            $this->info('Saved: ' . $object->title . ' -> ' . $file->file_name);
            $object->image()->add($file);
        }
        
    }

    public function getImage($wordpress_id) {
        return $this->db->table('wp_postmeta')
            ->join('wp_posts', 'wp_posts.ID', '=', 'wp_postmeta.meta_value')
            ->select('wp_posts.guid')
            ->where('meta_key', '_thumbnail_id')
            ->where('post_id', $wordpress_id)
            ->first();
    }
}