<?php namespace DMA\Friends\Components;

use Auth;
use Flash;
use Lang;
use Session;
use Cms\Classes\ComponentBase;
use DMA\Friends\Activities\ActivityCode;
use DMA\Friends\Activities\LikeWorkOfArt;

class ActivityCodeForm extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Activity Code Form',
            'description' => 'A form for users to enter Activity Codes'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onSubmit()
    {

        $params['code'] = post('activity_code');
        
        $user = Auth::getUser(); 

        // Try to process activity codes first
        $activity = ActivityCode::process($user, $params);

        // If still nothing see if its an assession id
        if (!$activity) {
            $activity = LikeWorkOfArt::process($user, $params);
        }

        $message = Session::pull('activityMessage');

        if ($message && $activity) {
            //TODO replace with advanced notification system when ready
            Flash::info($message);
        } else {
            Flash::error(Session::pull('activityError'));
        }

        return [
            '#flashMessages'    => $this->renderPartial('@flashMessages'),
            'span.points'       => $user->points,
        ];

    }

}
