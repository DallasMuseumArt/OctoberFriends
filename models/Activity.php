<?php namespace DMA\Friends\Models;

use Model;
use DateTime;

/**
 * Activity Model
 */
class Activity extends Model
{

    use \October\Rain\Database\Traits\Validation;

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
    protected $fillable = [];

    protected $dates = ['date_begin', 'date_end'];

    public $rules = [ 
        'title' => 'required',
    ]; 

    /**
     * @var array Relations
     */
    public $belongsToMany = [
        'steps'         => ['DMA\Friends\Models\Step'],
        'types'         => ['DMA\Friends\Models\ActivityType', 'table' => 'dma_friends_activity_activity_types'],
        'triggerTypes'  => ['DMA\Friends\Models\ActivityTriggerType', 'table' => 'dma_friends_activity_activity_trigger_type'],
    ];

    public $attachOne = [
        'image' => ['System\Models\File']
    ];

    public $morphMany = [ 
        'activityLogs'  => ['DMA\Friends\Models\ActivityLog', 'name' => 'object'],
    ];

    public function isActive()
    {
        return $this->is_published && !$this->is_archived;
    }

    /**
     * Mutator to ensure time_restriction_data is serialized
     */
    public function setTimeRestrictionDataAttribute($value)
    {
        if (is_array($value)) {
            return serialize($value);
        }

        return $value;
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

    public function scopefindWordpress($query, $id)
    {   
        return $query->where('wordpress_id', $id);
    }  
}
