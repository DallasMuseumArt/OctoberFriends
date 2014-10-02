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
        'user_id'       => 'required|numeric',
        'action'        => 'required|in:activity,artwork,points,reward,unlocked',
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

}
