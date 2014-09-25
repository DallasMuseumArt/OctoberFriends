<?php namespace DMA\Friends\Models;

use Model;

/**
 * Activity Model
 */
class Activity extends Model
{

    use \October\Rain\Database\Traits\Validation;

    const TIME_RESTRICT_NONE    = 0;
    const TIME_RESTRICT_HOURS   = 1;
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
        'activityLogs'  => ['DMA\Friends\Models\ActivityLog'],
    ];

    public function scopefindWordpress($query, $id)
    {   
        $query->where('wordpress_id', $id);
    }  
}
