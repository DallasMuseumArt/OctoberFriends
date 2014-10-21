<?php namespace DMA\Friends\Models;

use Model;

/**
 * Category Model
 */
class Category extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_categories';
    public $timestamps = false;

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

    public function beforeValidate()
    {   
        // Generate a URL slug for this model
        if (!$this->exists && !$this->slug)
            $this->slug = Str::slug($this->name);
    }  

    /** 
     * @var array Relations
     */
    public $morphMany = [ 
        'object' => ['table' => 'dma_friends_object_categories'],
    ]; 
}
