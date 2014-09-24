<?php namespace DMA\Friends\Models;

use Model;

/**
 * ActivityLog Model
 */
class ActivityLog extends Model
{

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

    public static $actionTypes = [
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

    // TODO: add morphing relationship to all object types that can be associated with a log entry
    public $morphTo = [
        'object',
    ];

}
