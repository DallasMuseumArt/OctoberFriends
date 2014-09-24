<?php namespace DMA\Friends\Models;

use Model;
use Str;

/**
 * ActivityTriggerType Model
 */
class ActivityTriggerType extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_activity_trigger_types';
    public $timestamps = false;

    public $rules = [ 
        'name' => 'required',
    ];

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

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
    public $hasMany = [
        'activities' => ['DMA\Friends\Models\Activity', 'table' => 'dma_friends_activity_activity_trigger_type'],
    ];
}
