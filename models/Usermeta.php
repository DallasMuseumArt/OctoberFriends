<?php namespace DMA\Friends\Models;

use Model;

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
        'user'  => 'RainLab\User\Model\User'
    ];

    public function scopeByPoints($query)
    {
        return $query->excludeStaff()->orderBy('points', 'desc');
    }

    public function scopeExcludeStaff($query)
    {
        return $query->where('current_member', '!=', self::IS_STAFF);
    }

}
