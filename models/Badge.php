<?php namespace DMA\Friends\Models;

use Model;
use RainLab\User\Models\User;

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

    public $morphToMany = [
        'categories'    => ['DMA\Friends\Models\Category', 'name' => 'object', 'table' => 'dma_friends_object_categories'],
    ];

    public function scopeNotCompleted($query, User $user)
    {
        return $query->join('dma_friends_badge_user', 'dma_friends_badges.id', '=', 'dma_friends_badge_user.badge_id')
            ->where('dma_friends_badge_user.user_id', '!=', $user->id)
            ->groupBy('dma_friends_badges.id');
    }

    public function scopefindWordpress($query, $id)
    {   
        return $query->where('wordpress_id', $id);
    }  

    public function steps()
    {
         return $this->hasMany('DMA\Friends\Models\Step');
    }
}
