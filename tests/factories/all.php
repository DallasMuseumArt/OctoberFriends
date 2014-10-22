<?php

use \Str as Str;
use League\FactoryMuffin\Facade as FactoryMuffin;
use DMA\Friends\Models\ActivityLog;
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
    'categories'        => 'factory|DMA\Friends\Models\Category',
]);

FactoryMuffin::define('DMA\Friends\Models\ActivityLog', [
    'user_id'           => 'factory|DMA\Friends\Models\User',
    'action'            => function($object, $saved) {
        $activityLog = new ActivityLog();
        $types = $activityLog->actionTypes;

        $rand = rand(0, count($types)-1);

        return $types[$rand];
    },
    'message'           => 'text',
    'object_type'       => 'optional:randomLetter|3',
    'object_id'         => 'optional:randomDigit',
    'points_earned'     => 'randomNumber',
    'total_points'      => 'randomNumber',
    'timestamp'         => 'dateTime|now',
    'timezone'          => 'timezone',
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
    'categories'                => 'factory|DMA\Friends\Models\Category',
]);

FactoryMuffin::define('DMA\Friends\Models\Category', [
    'name'          => 'word',
    'description'   => 'optional:text',
    'slug'          => function($object, $saved) {
        return Str::slug($object->title);
    },
]);

FactoryMuffin::define('DMA\Friends\Models\Location', [
    'title'         => 'word',
    'description'   => 'optional:text',
    'created_at'    => 'dateTime|now',
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

FactoryMuffin::define('DMA\Friends\Models\User', [
    'name'          => 'userName',
    'username'         => 'userName',
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
    'points'        => 'randomNumber',
]);

FactoryMuffin::define('DMA\Friends\Models\Usermeta', [
    'first_name'            => 'firstName',
    'last_name'             => 'lastName',
    'email_optin'           => 'boolean',
    'current_member'        => 'boolean',
    'current_member_number' => 'randomNumber',
]);
