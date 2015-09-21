<?php namespace DMA\Friends\Models;

use Model;

/**
 * UserRate Model
 */
class UserRate extends Model
{
 
    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_user_rates';


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
    public $belongsTo = [
        'rating' => ['DMA\Friends\Models\Rating',
                'key' => 'rating_id',
        ],
        'user' => ['RainLab\User\Models\User',
            'key' => 'user_id',         
       ],
    ];

	
}
