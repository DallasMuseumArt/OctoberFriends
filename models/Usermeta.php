<?php namespace DMA\Friends\Models;

use Model;
use RainLab\User\Models\User;
use RainLab\User\Models\State;

/**
 * Usermeta Model
 */
class Usermeta extends Model
{
    const NON_MEMBER    = 0;
    const IS_MEMBER     = 1;
    const IS_STAFF      = 2;

    public static $genderOptions = [
        'Male',
        'Female',
        'Trans Man',
        'Trans Women',
        'Non Binary/Other',
    ];

    public static $raceOptions = [
        'White',
        'Hispanic',
        'Black or African American',
        'American Indian or Alaska Native',
        'Asian',
        'Native Hawaiian or Other Pacific Islander',
        'Two or more races',
        'Other',
    ];

    public static $householdIncomeOptions = [
        'Less then 25k',
        '25k - 50k',
        '50k - 75k',
        '75k - 150k',
        '150k - 500k',
        '500k or more',
    ];

    public static $educationOptions = [
        'Never Completed High School',
        'High School/GED',
        'Some College',
        'Vocational or Trade School',
        'Bachelors Degree',
        'Masters Degree',
        'PhD',
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'dma_friends_usermetas';
    public $timestamps = false;

    protected $primaryKey = 'user_id';

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
        'user'          => ['RainLab\User\Models\User',
            'foreignKey'    => 'user_id',        
        ],
    ];

	/**
     * Automatically creates a metadata entry for a user if not one already.
     * @param  RainLab\User\Models\User $user
     * @return DMA\Friends\Models\Usermeta
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

    public static function getOptions()
    {
        $states = State::all();

        foreach ($states as $state) {
            $stateOptions[$state->id] = $state->name;
        }

        return [
            'gender'            => self::$genderOptions,
            'states'            => $stateOptions,
            'race'              => self::$raceOptions,
            'household_income'  => self::$householdIncomeOptions,
            'education'         => self::$educationOptions,
        ];
    }

}
