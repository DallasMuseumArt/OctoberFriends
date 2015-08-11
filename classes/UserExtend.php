<?php namespace DMA\Friends\Classes;

use Event;
use File as FileHelper;

use DMA\Friends\Classes\FriendsLog;
use RainLab\User\Models\User;
use SystemException;
use System\Models\File;
use libphonenumber\PhoneNumberUtil;
use libphonenumber\PhoneNumberFormat;
use Carbon\Carbon;
use October\Rain\Database\Attach\Resizer;
use DMA\Friends\Activities\Points;

/**
 * Custom class to add additional functionality based on the Rainlab User model
 * 
 * @package DMA\Friends\Classes
 * @author Carlos Arroyo, Kristen Arnold
 *
 * TODO: eventually we need to fork the rainlab plugin and incorporate these functions into
 * the model
 */
class UserExtend
{

    /**
     * @var The user object
     */
    public $user = null;

    /**
     * Extended functionality against user objects 
     * This is a really ugly way to extend the functionality
     * of the user object
     * 
     * @param \RainLab\User\Model\User (optional) If no user object
     * is provided then a new user object will be instantiated
     */
    public function __construct(User $user = null)
    {
        if (!$user)
            $user = new User;

        $this->user = $user;
    }

    /**
     * Add points to a users account
     *
     * @param integer $points
     * The amount of points to add to a user
     */
    public function addPoints($points)
    {
        if (!is_numeric($points))
            throw new SystemException('Points must be an integer');

        $this->user->points += $points;
        $this->user->points_this_week += $points;
        $this->user->points_today += $points;

        if ($this->user->forceSave()) {

            // Process any activities awarded on points achievement
            Points::process($this->user);
            
            Event::fire('dma.friends.user.pointsEarned', [$this->user, $points]);

            $params = [
                'user'          => $this->user,
                'points_earned' => $points,
            ];
            FriendsLog::points($params);
        }
    }

    /**
     * Remove points from a user account
     *
     * @param integer $points
     * The amount of points to remove from a user account
     *
     * @param boolean $deduct
     * If false points will not be deducted from leaderboard points
     *
     * @return boolean
     * returns true if points where removed
     */
    public function removePoints($points, $deduct = true)
    {
        if (!is_numeric($points))
            throw new SystemException('Points must be an integer');

        if ($this->user->points < $points) {
            return false;
        }

        $this->user->points -= $points;    

        if ($deduct) {    
            $this->user->points_this_week -= $points;
            $this->user->points_today -= $points;
        }
        
        if ($this->user->forceSave()) {
            Event::fire('dma.friends.user.pointsRemoved', [$this->user, $points]);
        }

        return true;
    }

    public static function uploadAvatar($user, $image)
    {

        $basename = basename($image);
        $src = base_path() . $image;
        $dst = '/tmp/' . $basename;

        if (is_dir($src)) return;

        copy($src, $dst);
        
        $file = new File;
        $file->data = $dst;
        $file->is_public = true;
        $file->save();

        if ($file) {
            $user->avatar()->add($file);
        }
    }

    /**
     * Upload Avatar from Base64 encoded image
     * 
     * @param \RainLab\User\Models\User $user
     * @param string $source
     * string contend of an image on Base64 enconding 
     */
    public static function uploadAvatarFromString($user, $source)
    {
        $dst = '/tmp/avatar_' . $user->getKey() . '_' . uniqid();
        
        FileHelper::put($dst, base64_decode($source));
        
        $validImage = true;
        try{
            // Validated is a JPG or PNG
            $imageType = exif_imagetype($dst);
            $validImage = in_array($imageType, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF]);
            // Validated is not bigger that xx by xx
            if($validImage){
                // Test if image is corrupted if OctoberCMS Resizer can open it
                // is more likely the image is ok
                Resizer::open($dst);
                
                // Test image dimensions
                list($width, $height, $type, $attr) = getimagesize($dst);
                $validImage = ($width <= 400 && $height <= 400);

            }
            // Add right file extension to the upload file
            if($validImage){
                
                // Save image with correct image extension
                $extension = [ IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif'][$imageType];
                $newDst = $dst . '.' . $extension;
                rename($dst, $newDst);
                $dst = $newDst;
                
            }
        } catch(\Exception $e){
            $validImage = false;
        }
        
        if(!$validImage){
            throw new \Exception('Must be a valid JPG, GIF or PNG. And not bigger that 400x400 pixels.');
        }

        $file = new File;
        $file->data = $dst;
        $file->is_public = true;
        $file->save();
    
        if ($file) {
            $user->avatar()->add($file);
        }
        
    }
    
    public function getMembershipStatusOptions()
    {
        return [
            UserGroup::MEMBERSHIP_PENDING   =>  UserGroup::MEMBERSHIP_PENDING,
            UserGroup::MEMBERSHIP_ACCEPTED  =>  UserGroup::MEMBERSHIP_ACCEPTED,
            UserGroup::MEMBERSHIP_REJECTED  =>  UserGroup::MEMBERSHIP_REJECTED,
            UserGroup::MEMBERSHIP_CANCELLED =>  UserGroup::MEMBERSHIP_CANCELLED
        ];
    }

    public static function parsePhone($phone)
    {
        if (empty($phone)) return null;

        $phoneUtil = PhoneNumberUtil::getInstance();
        
        // Get country code using configure timezone
        $tz = Carbon::now()->getTimezone();
        $country_code = array_get($tz->getLocation(), 'country_code', 'US');
        
        // Parse phone number
        $numberProto = $phoneUtil->parse($phone, $country_code);
        
        if ($phoneUtil->isValidNumber($numberProto)){
            return $phoneUtil->format($numberProto, PhoneNumberFormat::E164);
        }  

        return false;
    }

    public static function parseBirthdate($birthday)
    {
        return $birthday['year'] 
            . '-' .  sprintf("%02s", $birthday['month']) 
            . '-' .  sprintf("%02s", $birthday['day'])
            . ' 00:00:00';
    }
}
