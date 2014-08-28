<?php namespace DMA\Friends\Models;

use Model;

/**
 * Activity Model
 */
class Activity extends Model
{
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

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [
        'Step'
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    public function scopefindWordpress($query, $id)
    {   
        $query->where('wordpress_id', $id);
    }  
}
