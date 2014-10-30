<?php

return [
    'app' => [
        'name'                  => 'Friends',
        'activityCodeSuccess'   => 'Congradulations, you have completed ":title"',
        'activityCodeError'     => 'Sorry the code :code could not be found',
    ],
    'log' => [
        'activity'  => ':name just completed the activity of ":title"',
        'artwork'   => ':name liked the work of art :artwork_id',
        'checkin'   => ':name checked in at location ":title"',
        'points'    => ':name earned :points for a new total of :total_points',
        'reward'    => ':name claimed the reward ":title"',
        'unlocked'  => ':name unlocked the step ":title"',
    ],
    'exceptions' => [
        'missingActivityClass'      => 'Could not find the activity :class',
        'activityTypeNotInitiated'  => 'An activity type must be instantiated before form fields can be used',
    ],
];
