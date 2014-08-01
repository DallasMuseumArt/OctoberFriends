<?php namespace DMA\Friends\Models;

use Model;

/**
 * Usermeta Model
 */
class Usermeta extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_usermetas';
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
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

}
