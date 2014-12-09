<?php namespace DMA\Friends\Models;

use Model;
use RainLab\User\Models\User;
use DMA\Friends\Models\Activity;

/**
 * ActivityAudit Model
 *
 */
class ActivityAudit extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_activity_audit';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    protected $dates = [];

    public $rules = []; 

    public $belongsTo = [
        'user'      => ['\RainLab\User\Models\User'],
        'activity'  => ['\DMA\Friends\Models\Activity']
    ];

    
    public static function addUserActivity(User $user, Activity $activity, array $data)
    {
        $rows = [];
        foreach($data as $key => $value){
            $row = [
                'user_id'      => $user->getKey(),
                'activity_id'  => $activity->getKey(),
                'key'          => $key,
                'value'        => $value 
            ];
            $rows[] = $row;
        }
        
        if (count($row) > 0){
            ActivityAudit::insert($rows);
        }
        
    }


}
