<?php namespace DMA\Friends\Activities;

use RainLab\User\Models\User;
use DMA\Friends\Models\Activity;
use DMA\Friends\Classes\ActivityTypeBase;

class LikeWorkOfArt extends ActivityTypeBase
{

    /**
     * {@inheritDoc}
     */
    public function details()
    {
        return [
            'name'          => 'Like a work of art',
            'description'   => 'enter an assession id to like a work of art',
        ];
    }

    /**
     * {%inheritDoc}
     */
    public static function process(User $user, $params)
    {
        if (!isset($params['code']) || empty($params['code'])) return false;

        if (!self::isAssessionNumber($params['code'])) return false;

        if ($activity = Activity::findActivityType('LikeWorkOfArt')->first()) {
            return parent::process($user, ['activity' => $activity]);
        }

        return false;

    }

    /**
     * Take a string of text and determine if it is an assession #
     * TODO: This function will eventually be return to provide some sort of
     * proper validation to ensure that we are actually pulling a valid
     * assession number
     *
     * @param string A string of text to check 
     *
     * @return boolean True if code is an assession #
     */
    public static function isAssessionNumber($code)
    {
        return preg_match( '/^[a-zA-Z0-9]{2}[a-zA-Z0-9]?[a-zA-Z0-9]?\..+$/', $code );
    }
}