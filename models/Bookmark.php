<?php namespace DMA\Friends\Models;

use Model;
use RainLab\User\Models\User;
use DMA\Friends\Models\Bookmark;

/**
 * Bookmark Model
 */
class Bookmark extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_bookmarks';

    public $timestamps = false;

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['user', 'object'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user' => ['\RainLab\User\Models\User']
    ];

    public $morphTo = [
        'object' => ['id' => 'object_id'],
    ];

    public static function findBookmark($user, $object)
    {
        $bookmark = self::where('user_id', '=', $user->id)
            ->where('object_id', '=', $object->id)
            ->where('object_type', '=', get_class($object))
            ->first();

        return $bookmark;
    }

    public static function saveBookmark(User $user, $object)
    {        
        $bookmark = new Bookmark();
        $object->bookmarks()->save($bookmark);
        $user->bookmarks()->save($bookmark);
    }

    public static function removeBookmark(User $user, $object)
    {        
        self::where('user_id', '=', $user->id)
            ->where('object_id', '=', $object->id)
            ->where('object_type', '=', get_class($object))
            ->delete();
    }
}