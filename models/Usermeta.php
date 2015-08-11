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

    /**
     * @var array $genderOptions
     * Provide a list of gender options for the user profile
     */
    public static $genderOptions = [
        'Male',
        'Female',
        'Non Binary/Other',
    ];

    /**
     * @var array $raceOptions
     * Provide a list of race options for the user profile
     */
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

    /**
     * @var array $householdIncomeOptions
     * Provide a list of income options for the user profile
     */
    public static $householdIncomeOptions = [
        'Less then $25,000',
        '$25,000 - $50,000',
        '$50,000 - $75,000',
        '$75,000 - $150,000',
        '$150,000 - $500,000',
        '$500,000 or more',
    ];

    /**
     * @var array $educationOptions
     * Provide a list of education options for the user profile
     */
    public static $educationOptions = [
        'K-12',
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

    /**
     * @var boolean $timestamps
     * Do not use timestamps on the usermeta object
     */
    public $timestamps = false;

    public $primaryKey = 'user_id';

    /**
     * @var string $key
     */
    protected $key = 'user_id';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['user_id'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'birth_date',
        'email_optin',
        'gender',
        'race',
        'household_income',
        'household_size',
        'education',
        'current_member',
        'current_member_number',
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user' => ['RainLab\User\Models\User',
            'key' => 'user_id',        
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
    
    /**
     * Return users that are not staff and order them by points descending
     */
    public function scopeByPoints($query)
    {
        return $query->excludeStaff()->orderBy('points', 'desc');
    }

    /**
     * Exclude staff members from results
     */
    public function scopeExcludeStaff($query)
    {
        return $query->where('current_member', '!=', self::IS_STAFF);
    }

    /**
     * @return array
     * Return all demographic options 
     */
    public static function getOptions()
    {
        $states = State::where('country_id', '=', 1)->get();

        return [
            'gender'            => self::$genderOptions,
            'states'            => $states,
            'race'              => self::$raceOptions,
            'household_income'  => self::$householdIncomeOptions,
            'education'         => self::$educationOptions,
        ];
    }

}
