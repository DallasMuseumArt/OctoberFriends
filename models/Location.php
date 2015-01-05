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
    protected $fillable = ['uuid'];

    public $rules = [];

    /**
     * @var array Relations
     */
    public $morphMany = [ 
        'activityLogs'  => ['DMA\Friends\Models\ActivityLog', 'name' => 'object'],
    ];

    public function scopeFindByUUID($query, $uuid)
    {
        return $query->where('uuid', $uuid);
    }

    public function scopeHasMemberPrinter($query)
    {
        return $query->where('printer_membership', '!=', '');
    }

    public function scopeHasRewardPrinter($query)
    {
        return $query->where('printer_reward', '!=', '');
    }

    public function scopefindWordpress($query, $id)
    {   
        return $query->where('wordpress_id', $id);
    } 
}
