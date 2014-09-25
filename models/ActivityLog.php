<?php namespace DMA\Friends\Models;

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
        'action'        => 'required|in:activity,artwork,points,reward,unlocked',
        'user_id'       => 'required|numeric',  
        'points_earned' => 'numeric',
        'total_points'  => 'numeric',
        'timestamp'     => 'required',
        'timezone'      => 'required|timezone',
    ];

    /**
     * The list of acceptable action types
     */
    public $actionTypes = [
        'activity',
        'artwork',
        'points',
        'reward',
        'unlocked',
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'User' => '\RainLab\User\Model\User'
    ];

    public $morphTo = [
        'object',
    ];

}
