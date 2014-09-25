<?php namespace DMA\Friends\Models;

use Model;
//use DMA\Friends\Models\Step;
//use RainLab\User\Models\User;

/**
 * Badge Model
 */
class Badge extends Model
{

    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_badges';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['touch'];

    protected $dates = ['date_begin', 'date_end'];

    public $rules = [ 
        'title' => 'required',
    ]; 

    /**
     * @var array Relations
     */
    public $hasMany = [
        'steps' => ['DMA\Friends\Models\Step'],
    ];
    public $belongsToMany = [
        'users' => ['RainLab\User\Models\User', 'dma_friends_badge_user'],
    ];
    public $attachOne = [
        'image' => ['System\Models\File']
    ];

    public $morphMany = [
        'activityLogs'  => ['DMA\Friends\Models\ActivityLog', 'name' => 'object'],
    ];

    public function scopefindWordpress($query, $id)
    {   
        $query->where('wordpress_id', $id);
    }  

    public function steps()
    {
         return $this->hasMany('DMA\Friends\Models\Step');
    }
}
