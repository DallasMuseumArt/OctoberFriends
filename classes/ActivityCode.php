<?php
namespace DMA\Friends\Classes;

use DMA\Friends\Models\User;
use DMA\Friends\Models\Activity;
use DMA\Friends\Classes\ActivityProcessor;

class ActivityCode extends ActivityProcessor
{

    /**
     * @see \DMA\Friends\Classes\ActivityProcessor
     *
     * Process and determine if an award can be issued
     * based on a provided activity code
     *
     * @param object $user
     * A user model for which the activity should act upon
     * 
     * @param array $params
     * An array of parameters for validating activities 
     *
     * @return boolean
     * returns true if the process was successful
     */
    public static function process(User $user, $params)
    {
        if (!isset($params['code']) || empty($params['code'])) return false;

        if ($activity = Activity::findCode($params['code'])->first()) {
            return parent::process($user, ['activity' => $activity]);
        }

        return false;

    }
}
