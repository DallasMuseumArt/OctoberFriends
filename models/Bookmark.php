<?php namespace DMA\Friends\Models;

use Model;

/**
 * Bookmark Model
 */
class Bookmark extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_bookmarks';

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
    public $hasOne = [
        'user'  => ['RainLab\User\Models\User'],
        'badge' => ['DMA\Friends\Models\Badge'],
    ];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}