<?php namespace DMA\Friends\Models;

use Model;
use RainLab\User\Models\User;

/**
 * ActivityRating Model
 *
 */
class Rating extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_ratings';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];


    public $rules = []; 
   
    public $morphTo = [
            'object' => ['id' => 'object_id'],
    ];
    
    /**
     * @var array Relations
     */
    public $hasMany = [
            'rates' => ['DMA\Friends\Models\UserRate', 'table' => 'dma_friends_user_rates'],
    ];
    
    

    /**
     * Recalculate ratings stats ( total and average )
     */
    public function reCalculateRating()
    {
        $this->total = $this->rates()->count();
        $this->average = $this->rates()->avg('rate');
        $this->save();
    }
    
    

   

}
