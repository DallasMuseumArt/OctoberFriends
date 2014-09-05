<?php namespace DMA\Friends\Models;

use Model;

/**
 * ActivityTriggerType Model
 */
class ActivityTriggerType extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_activity_trigger_types';
    public $timestamps = false;

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [
        'activities'    => ['DMA\Friends\Models\Activity'],
    ];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}
