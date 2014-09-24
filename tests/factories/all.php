<?php

use \Str as Str;
use League\FactoryMuffin\Facade as FactoryMuffin;
use DMA\Friends\Models\ActivityType as ActivityType;
use RainLab\User\Models\Country;
use RainLab\User\Models\State;

FactoryMuffin::define('DMA\Friends\Models\Activity', [
    'title'             => 'sentence',
    'description'       => 'optional:text',
    'excerpt'           => 'optional:text',
    'points'            => 'randomNumber|4',
    'image'             => 'optional:imageUrl|400;600',
    'activity_code'     => 'randomLetter|3',
    'activity_lockout'  => 'randomDigitNotNull|4',
    'time_restriction'  => 'randomNumber|2',
    'is_published'      => 'boolean',
    'is_archived'       => 'boolean',
    'created_at'        => 'dateTime|now',
    'date_begin'        => 'optional:dateTime',
    'date_end'          => 'optional:dateTime',
    'triggerTypes'      => 'factory|DMA\Friends\Models\ActivityTriggerType',
]);

FactoryMuffin::define('DMA\Friends\Models\ActivityLog', [
    'user_id'           => 'factory|RainLab\User\Models\User',
    'action'            => function($object, $saved) {
        $rand = rand(0, 3);
        $types = ActivityType::actionTypes;
        return $types[$rand];
    },
    'message'           => 'sentence',
    'object_type'       => 'optional:randomLetter|3',
    'object_id'         => 'optional:randomDigit',
    'points_earned'     => 'randomNumber',
    'total_points'      => 'randomNumber',
    'timestamp'         => 'dateTime|now',
]);

FactoryMuffin::define('DMA\Friends\Models\ActivityTriggerType', [
    'name'          => 'word',
    'description'   => 'optional:text',
    'slug'          => function($object, $saved) {
        return Str::slug($object->title);
    },
]);

FactoryMuffin::define('DMA\Friends\Models\Badge', [
    'title'                     => 'sentence',
    'description'               => 'optional:text',
    'image'                     => 'optional:imageUrl|400;600',
    'excerpt'                   => 'optional:text',
    'congratulations_text'      => 'optional:text',
    'points'                    => 'randomNumber|3',
    'maximum_earnings'          => 'randomNumber|3',
    'steps'                     => 'factory|DMA\Friends\Models\Step',
    'is_sequential'             => 'boolean',
    'show_earners'              => 'boolean',
    'time_between_steps_min'    => 'randomDigitNotNull|8',
    'time_between_steps_max'    => 'randomDigitNotNull|8',
    'maximium_time'             => 'randomDigitNotNull|2',
    'date_begin'                => 'optional:dateTime',
    'date_end'                  => 'optional:dateTime',
    'is_published'              => 'boolean',
    'is_archived'               => 'boolean',
    'created_at'                => 'dateTime|now',
]);

FactoryMuffin::define('DMA\Friends\Models\Reward', [
    'title'             => 'sentence',
    'description'       => 'optional:text',
    'excerpt'           => 'optional:text',
    'fine_print'        => 'optional:text',
    'points'            => 'randomDigitNotNull',
    'image'             => 'optional:imageUrl|400;600',
    'barcode'           => 'randomLetter|3',
    'date_begin'        => 'optional:dateTime',
    'date_end'          => 'optional:dateTime',
    'days_valid'        => 'optional:randomDigit|2',
    'inventory'         => 'optional:randomDigit|3',
    'enable_email'      => 'boolean',
    'redemption_email'  => 'optional:text',
    'is_published'      => 'boolean',
    'is_archived'       => 'boolean',
    'hidden'            => 'boolean',
    'created_at'        => 'dateTime|now',
 
]);

FactoryMuffin::define('DMA\Friends\Models\Step', [
    'title'         => 'sentence',
    'created_at'    => 'dateTime|now',
    'updated_at'    => 'dateTime|now',
]);

FactoryMuffin::define('RainLab\User\Models\User', [
    'name'          => 'userName',
    'email'         => 'email',
    'password'      => 'password',
    'password_confirmation' => 'password',
    'is_activated'  => 'boolean',
    'activated_at'  => 'dateTime',
    'last_login'    => 'dateTime',
    'country'       => function($object, $saved) {
        return Country::orderByRaw('RAND()')->first();
    },
    'state'         => function($object, $saved) {
        return State::orderByRaw('RAND()')->first();
    },
    'created_at'    => 'dateTime|now',
    'updated_at'    => 'dateTime|now',
    'phone'         => 'optional:phone',
    'company'       => 'optional:company',
    'street_addr'   => 'streetAddress',
    'city'          => 'city',
    'zip'           => 'postcode',
]);

FactoryMuffin::define('DMA\Friends\Models\Usermeta', [
    'points'                => 'randomNumber',
    'first_name'            => 'firstName',
    'last_name'             => 'lastName',
    'email_optin'           => 'boolean',
    'current_member'        => 'boolean',
    'current_member_number' => 'randomNumber',
]);
