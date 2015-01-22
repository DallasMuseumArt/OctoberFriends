<?php namespace DMA\Friends\Models;

use Smirik\PHPDateTimeAgo\DateTimeAgo as TimeAgo;
use Model;

/**
 * ActivityLog Model
 */
class ActivityLog extends Model
{

    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_activity_logs';
    public $timestamps = false;
    protected $dates = ['timestamp'];

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    protected $rules = [
        'message'       => 'min:10',
        'user_id'       => 'required|numeric',
        'action'        => 'required|in:activity,artwork,checkin,points,reward,unlocked',
        'points_earned' => 'numeric',
        'total_points'  => 'numeric',
        'timestamp'     => 'required',
    ];

    /**
     * The list of acceptable action types
     */
    public $actionTypes = [
        'activity',
        'artwork',
        'checkin',
        'points',
        'reward',
        'unlocked',
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user' => ['\RainLab\User\Models\User']
    ];

    public $morphTo = [
        'object',
    ];

    public function scopeByUser($query, $user_id)
    {
        return $query->where('user_id', $user_id);
    }

    /**
     * Mutator function to return the pivot timestamp as time ago
     * @return string The time since the badge was earned
     */
    public function getTimeAgoAttribute($value)
    {
        $timeAgo = new TimeAgo;
        return $timeAgo->get($this->timestamp);
    }
}
