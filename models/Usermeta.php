<?php namespace DMA\Friends\Models;

use Model;
use \RainLab\User\Models\User;

/**
 * Usermeta Model
 */
class Usermeta extends Model
{
    const NON_MEMBER = 0;
    const IS_MEMBER = 1;
    const IS_STAFF = 2;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_usermetas';
    public $timestamps = false;

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
        'user'  => 'RainLab\User\Models\User',
        'primaryKey' => 'group_id',
        'foreignKey' => 'user_id',        
    ];

	/**
     * Automatically creates a metada entry for a user if not one already.
     * @param  RainLab\User\Models\User $user
     * @return Dma\Friends\Models\Usermeta
     */
    public static function getFromUser($user = null)
    {
        if (!$user)
            return null;

        if (!$user->metadata) {

            $meta = new static;            
            User::find($user->getKey())->metadata()->save($meta);
            $user = User::find($user->getKey());
            
        }

        return $user->metadata;
    }
    
    public function scopeByPoints($query)
    {
        return $query->excludeStaff()->orderBy('points', 'desc');
    }

    public function scopeExcludeStaff($query)
    {
        return $query->where('current_member', '!=', self::IS_STAFF);
    }

}
