<?php namespace DMA\Friends\Models;

use Model;

/**
 * ActivityLog Model
 */
class ActivityStream extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_activity_stream';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    public $morphTo = [
        'object',
    ];

    public function scopeUser($query, $user_id)
    {
        return $query->where('user_id', $user_id);
    }

}
