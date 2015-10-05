<?php namespace DMA\Friends\Models;

use Model;
use Hashids\Hashids;

/**
 * Application Model
 *
 */
class Application extends Model
{

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_api_applications';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];


    public $rules = []; 
   
    public $morphTo = [];
    
    /**
     * @var array Relations
     */
    public $hasMany = [];
    

    // BASIC API DATA ACCESS CONSTANTS
    const ACCESS_ALL_DATA    = 'ALL_DATA';
    const ACCESS_USER_DATA   = 'USER_DATA';


    
    
    /**
     * {@inheritDoc}
     */
    public static function boot()
    {
        parent::boot();
    
        // Setup event bindings...
        //UserGroup::observe(new UserGroupObserver);
    
    
        /**
         * Attach to the 'creating' Model Event to generated
         * an application key
        */
        static::saved(function ($model) {
            if(!$model->app_key){
                $model->app_key = $model->generateAppKey();
                $model->save();
            }
        });
    }
    
    
    /**
     * 
     * @param string $appID
     */
    public function scopeIsActive($query)
    {
        return $query->where('is_active', '=', 1);
    }
    
    /**
     * Generate a unique application key.
     * This key must used to authenticated users
     * @return string
     */
    public function generateAppKey()
    {
        $hashids = new Hashids('friends.api', 16, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
        $id = $this->getKey();
        $key = $hashids->encode($id, time());
        return $key;
    }
    
    
    // OPTIONS
    public function getAccessLevelOptions($keyValue = null)
    {
        return [
            self::ACCESS_USER_DATA => 'Allow to access only user own data.', 
            self::ACCESS_ALL_DATA  => 'Allow access to all data.',    
        ];
    }

   

}
