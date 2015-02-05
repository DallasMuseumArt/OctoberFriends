<?php namespace DMA\Friends\Models;

use Model;
use RainLab\User\Models\User;
use DMA\Friends\Models\Activity;
use Hashids\Hashids;

/**
 * ActivityMetadata Model
 *
 */
class ActivityMetadata extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_activity_metadata';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    protected $dates = [];

    public $rules = []; 

    public $belongsTo = [
        'user'      => ['\RainLab\User\Models\User'],
        'activity'  => ['\DMA\Friends\Models\Activity']
    ];

    
    /**
     * Add activity metadata 
     * 
     * @param User $user
     * @param Activity $activity
     * @param array $data    key-value pair data to store
     * @param array $exclude When given exclude keys to be stored in the database
     */
    public static function addUserActivity(User $user, Activity $activity, array $data, array $exclude=[])
    {
        $rows = [];
        $exclude = array_map('strtolower', $exclude);
        
        // Create a session_id.
        // Session_id is use for easily identify groups of metadata 
        $hashids = new Hashids('dma.activity.metadata', 6);
        $user_id        = $user->getKey();
        $activity_id    = $activity->getKey();
        
        // Add unixtime and microseconds to avoid session_id collisions 
        $micro       = microtime(true);
        $unixtime    = floor($micro);
        $milseconds  = floor(($micro - $unixtime) * pow(10, 8)); 
        
        // Create session_id
        $session_id     = $hashids->encode($user_id, $activity_id, $unixtime, $milseconds);
        
        // Current date and time
        $now            = date('Y-m-d H:i:s');
        
        foreach($data as $key => $value){
            $key = strtolower($key);
            if (!in_array($key, $exclude)) {
                $row = [
                    'session_id'    =>  $session_id,
                    'user_id'       =>  $user_id,
                    'activity_id'   =>  $activity_id,
                    'key'           =>  $key,
                    'value'         =>  $value,
                    'created_at'    =>  $now,
                    'updated_at'    =>  $now
                ];
                $rows[] = $row;
            }
        }

        if (count($row) > 0){
            static::insert($rows);
        }
        
    }
    
    
    /**
     * Query scope to filter activity metadata by the given key and value
     * @param mixed $query
     * @param RainLab\User\Models\User $user
     * @param DMA\Friends\Models\Activity $activity
     * @param string $key
     * @param string $value
     */
    public function scopeHasMetadataValue( $query, $user, $activity, $key, $value) 
    {
        return self::where('user_id',       $user->getKey())
                    ->where('activity_id',  $activity->getKey())
                    ->where('key',          $key)
                    ->where('value',        $value);
    }

}
