<?php namespace DMA\Friends\Models;

use Model;

/**
 * Location Model
 */
class Location extends Model
{

    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_locations';

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
    public $morphMany = [ 
        'activityLogs'  => ['DMA\Friends\Models\ActivityLog', 'name' => 'location'],
    ];

    public function scopefindWordpress($query, $id)
    {   
        $query->where('wordpress_id', $id);
    }  
}
