<?php namespace DMA\Friends\Models;

use Model;
use DateTime;
use DB;
use Smirik\PHPDateTimeAgo\DateTimeAgo as TimeAgo;

/**
 * Activity Model
 *
 * NOTE: Time restrictions conform to the ISO-8601 numeric representation of the day of the week 
 * where 1 (for Monday) through 7 (for Sunday) see <a href="http://php.net/manual/en/function.date.php">PHP's Date Manual</a>
 */
class Activity extends Model
{

    use \October\Rain\Database\Traits\Validation;
    use \DMA\Friends\Traits\Rateable;

    /**
     * @const No time restriction set
     */
    const TIME_RESTRICT_NONE    = 0;
    /**
     * @const A time restriction is set by individual hours and days of the week
     */
    const TIME_RESTRICT_HOURS   = 1;
    /**
     * @const A time restriction is set by a date range
     */
    const TIME_RESTRICT_DAYS    = 2;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_activities';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['touch'];

    protected $dates = ['date_begin', 'date_end'];

    public $rules = [ 
        'title'             => 'required',
        'activity_code'     => 'unique:dma_friends_activities',
        'complete_message'  => 'max:159'
    ]; 

    /**
     * @var array Relations
     */
    public $hasMany = [
        'steps' => ['DMA\Friends\Models\Step', 'table' => 'dma_friends_activity_step'],
    ];

    public $belongsToMany = [
        'users' => ['RainLab\User\Models\User', 'table' => 'dma_friends_activity_user', 'timestamps' => true],
    ];

    public $attachOne = [
        'image' => ['System\Models\File'],
    ];

    public $morphMany = [ 
        'activityLogs'  => ['DMA\Friends\Models\ActivityLog', 'name' => 'object'],
    ];
    
    public $morphToMany = [
        'categories'    => ['DMA\Friends\Models\Category', 
            'name'  => 'object', 
            'table' => 'dma_friends_object_categories',
            'order' => 'name',
        ],
    ];

    /**
     * Mutator to ensure time_restriction_data is serialized
     */
    public function setTimeRestrictionDataAttribute($value)
    {
        if (is_array($value)) {
            $value = serialize($value);
        }

        $this->attributes['time_restriction_data'] = $value;
    }

    /**
     * Accessor to unserialize time_restriction_data attribute
     */
    public function getTimeRestrictionDataAttribute($value)
    {
        return unserialize($value);
    }

    /**
     * Return only activities that are active
     */
    public function scopeIsActive($query)
    {
        return $query->where('is_published', '=', 1)
            ->where('is_archived', '<>', 1);
    }

    /**
     * Find an activity by its activity code
     */
    public function scopefindCode($query, $code)
    {
        return $query->where('activity_code', $code)
            ->isActive();
    }

    /**
     * Find activities by activity type
     */
    public function scopeFindActivityType($query, $type)
    {
        return $query->where('activity_type', $type)
            ->isActive();
    }

    /**
     * Find activities by a wordpress id if they where imported
     */
    public function scopefindWordpress($query, $id)
    {   
        return $query->where('wordpress_id', $id);
    }  

    /**
     * Find activities by category
     * @param  string|array $categories string or array of strings of category name(s)
     */
    public function scopeByCategory($query, $categories) {
        if (is_array($categories)) {
            // Get first category in list to begin query with whereHas
            $firstcategory = array_shift($categories);
            $query = $query->whereHas('categories', function($q) use ($firstcategory) {
                $q->where('name', $firstcategory);
            });

            // if any categories left in list, iterate over for orWhereHas
            foreach ($categories as $category) {
                $query = $query->orWhereHas('categories', function($q) use ($category) {
                    $q->where('name', $category);
                });
            }
            
            return $query;
        }
        else {
            // if $categories is a string, only one category to filter against
            return $query->whereHas('categories', function($q) use ($categories) {
                $q->where('name', $categories);
            });
        }
    }

    /**
     * Find activities whose end dates have not passed (or which do not have end dates)
     */
    public function scopeNotExpired($query) {
        $query = $query->where(function($q) {
            $q->where('time_restriction', '!=', 2)
              ->orWhere('date_end', '>', date('Y-m-d H:i:s'));
        });

        return $query;
    }

    /**
     * Find activities whose end dates have not passed,
     * AND whose begin dates have passed 
     * (or which do not have begin and end dates)
     */
    public function scopeStartedNotExpired($query) {
        $query = $query->where(function($q) {
            $q->where('time_restriction', '!=', 2)
              ->orWhere(function($q) {
                $q->where('date_end', '>', date('Y-m-d H:i:s'))
                  ->where('date_begin', '<', date('Y-m-d H:i:s'));
              });
        });

        return $query;
    }

    /**
     * Mutator function to return the pivot timestamp as time ago
     * @return string The time since the badge was earned
     */
    public function getTimeAgoAttribute($value)
    {
        if (!isset($this->pivot->created_at)) return null;

        $timeAgo = new TimeAgo;
        return $timeAgo->get($this->pivot->created_at);
    }

}
