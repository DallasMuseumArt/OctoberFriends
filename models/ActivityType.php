<?php namespace DMA\Friends\Models;

use Model;
use Str;

/**
 * ActivityType Model
 */
class ActivityType extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_activity_types';
    public $timestamps = false;

    public $rules = [ 
        'name' => 'required',
        'slug' => ['required', 'regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i'],
    ];  

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    public function beforeValidate()
    {   
        // Generate a URL slug for this model
        if (!$this->exists && !$this->slug)
            $this->slug = Str::slug($this->name);
    } 

    /**
     * @var array Relations
     */
    public $hasMany = [
        'activities'    => ['DMA\Friends\Models\Activity'],
    ];
}
