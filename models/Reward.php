<?php namespace DMA\Friends\Models;

use Model;
use Smirik\PHPDateTimeAgo\DateTimeAgo as TimeAgo;

/**
 * Reward Model
 */
class Reward extends Model
{

    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_rewards';

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
        'title' => 'required',
    ];  

    /**
     * @var array Relations
     */
    public $belongsToMany = [
        'users' => ['Rainlab\User\Models\User', 'dma_friends_reward_user'],
    ];

    public $attachOne = [
        'image' => ['System\Models\File']
    ];

    public $morphMany = [ 
        'activityLogs'  => ['DMA\Friends\Models\ActivityLog', 'name' => 'object'],
    ];

    public function scopefindWordpress($query, $id)
    {   
        return $query->where('wordpress_id', $id);
    }  

    public function scopeIsActive($query)
    {
        return $query->where('is_published', '=', 1)
            ->where('is_archived', '=', 0)
            ->where('is_hidden', '=', 0)
            ->whereNull('inventory')
            ->orWhere('inventory', '>', 0);
    }

    public function getPointsFormatted()
    {
        return number_format($this->points);
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
