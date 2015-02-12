<?php
namespace DMA\Friends\Commands;

use DB;
use Log;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use DMA\Friends\Models\Reward;
use DMA\Friends\Models\Badge;
use RainLab\User\Models\User;
use System\Models\File;
use Cms\Classes\Theme;

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
        try {
            $this->db = DB::connection('friends_wordpress');
        } catch (\InvalidArgumentException $e) {
            Log::info('Missing configuration for wordpress migration');
        }

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
                    $image = $this->getPostImage($badge->wordpress_id);

                    if (isset($image->guid)) {
                        $badge->image()->delete();
                        $this->processImage($badge->image(), $image->guid);
                    }
                }
            }
        });

        $this->info('Importing reward images');

        Reward::where('wordpress_id', '>', 0)->chunk(200, function($rewards) {
            foreach ($rewards as $reward) {
                if (isset($reward->wordpress_id)) {
                    $image = $this->getPostImage($reward->wordpress_id);

                    if (isset($image->guid)) {
                        $reward->image()->delete();
                        $this->processImage($reward->image(), $image->guid);
                    }
                }
            }
        });

        $this->info('Importing user avatars');

        $avatarPath = $this->getAvatarDir();

        User::chunk(200, function($users) use ($avatarPath) {
            foreach ($users as $user) {
                $image = $this->getUserImage($user->id);
                if (!empty($image->avatar)) {
                    $user->avatar()->delete();
                    $path = $avatarPath . $image->avatar . '.jpg';
                    $this->processImage($user->avatar(), $path);
                }
            }
        });

    }

    public function processImage($objectImage, $image) 
    {
        $basename = basename($image);
        $dst = '/tmp/' . $basename;
        copy($image, $dst);
        
        $file = new File;
        $file->data = $dst;
        $file->is_public = true;
        $file->save();

        if ($file) {
            $this->info('Saved: ' . $file->file_name);
            $objectImage->add($file);
        }
        
    }

    public function getPostImage($wordpress_id) 
    {
        return $this->db->table('wp_postmeta')
            ->join('wp_posts', 'wp_posts.ID', '=', 'wp_postmeta.meta_value')
            ->select('wp_posts.guid')
            ->where('meta_key', '_thumbnail_id')
            ->where('post_id', $wordpress_id)
            ->first();
    }

    public function getUserImage($wordpress_id) 
    {
        return $this->db->table('wp_usermeta')
            ->select('meta_value AS avatar')
            ->where('meta_key', 'avatar')
            ->where('user_id', $wordpress_id)
            ->first();
    }

    public function getAvatarDir()
    {
        $activeTheme = Theme::getActiveTheme();
        $themeDir = $activeTheme->getDirName();
        return base_path() . '/themes/' . $themeDir . '/assets/images/avatars/';
    }
}