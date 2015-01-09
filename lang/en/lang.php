<?php

return [
    'app' => [
        'name'          => 'Friends',
        'stepTitle'     => 'Complete activity ":title" :count times',
        'loginFailed'   => 'Login Failed',
    ],
    'activities' => [
        'codeSuccess'   => 'Congratulations, you have completed ":title"',
        'codeError'     => 'Sorry the code :code could not be found',
        'lockout'       => 'You cannot use this code for :x minutes',
        'notAvailable'  => 'Sorry this activity is currently not available',
    ],
    'badges' => [
        'completed'     => 'Congratulations, you have been awarded the badge ":title"',
        'noBadges'      => 'You have no badges',
    ],
    'rewards' => [
        'redeemed'      => 'Congratulations, you have redeemed the reward ":title"',
        'noPoints'      => 'Sorry you do not have enough points to redeem this reward',
        'couponText'    => 'Thank you for being a DMA Friend!',
    ],
    'log' => [
        'activity'  => ':name just completed the activity of ":title"',
        'artwork'   => ':name liked the work of art :artwork_id',
        'checkin'   => ':name checked in at location ":title"',
        'points'    => ':name earned :points for a new total of :total_points',
        'reward'    => ':name claimed the reward ":title"',
        'unlocked'  => ':name unlocked the step ":title"',
    ],
    'user' => [
        'save'                  => 'Successfully saved user profile',
        'saveFailed'            => 'Unable to save user profile',
        'passwordSave'          => 'Your password has been successfully updated',
        'passwordFail'          => 'The password you entered is incorrect',
        'memberCard'            => 'The card has been sent to the designated membership printer for ":title"',
        'memberCardLocation'    => 'Select a location to print the membership card',
    ],
    'userLogin' => [
        'loginTitle'    => 'Scan Membership Card',
        'registerTitle' => 'Sign Up for Friends',
    ],
    'exceptions' => [
        'missingActivityClass'      => 'Could not find the activity :class',
        'missingReward'             => 'Could not find reward ":id"',
        'rewardFailed'              => 'Failed to redeem reward',
        'activityTypeNotInitiated'  => 'An activity type must be instantiated before form fields can be used',
        'stepFailed'                => 'Failed to complete step',
        'badgeFailed'               => 'Failed to complete badge',
        'printerSettingsMissing'    => 'Invalid printer settings. Please check your configuration',
    ],
];
