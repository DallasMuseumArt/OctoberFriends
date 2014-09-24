<?php namespace DMA\Friends\Models;

use Model;

/**
 * step Model
 */
class Step extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_steps';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['touch'];

    /**
     * @var array Relations
     */
    public $hasMany = [
        'Activity',
    ];

    public $belongsToMany = [
        'users' => ['Rainlab\User\Models\User', 'dma_friends_step_user'],
    ];

    public function scopefindWordpress($query, $id)
    {
        $query->where('wordpress_id', $id);
    }

}
