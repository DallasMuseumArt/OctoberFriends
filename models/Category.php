<?php namespace DMA\Friends\Models;

use Model;

/**
 * Category Model
 */
class Category extends Model
{
    use \October\Rain\Database\Traits\Sluggable;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_categories';

    /**
     * @var No timestamps needed here
     */
    public $timestamps = false;

    /**
     * @var Sluggable fields
     */
    public $slugs = ['slug' => 'name'];

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    public $rules = [ 
        'name' => 'required',
    ];

    /** 
     * @var array Fillable fields
     */
    protected $fillable = ['touch'];

    /** 
     * @var array Relations
     */
    public $morphMany = [ 
        'objects' => ['table' => 'dma_friends_object_categories'],
    ]; 
}
