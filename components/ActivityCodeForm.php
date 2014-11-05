<?php namespace DMA\Friends\Components;

use Auth;
use Flash;
use Lang;
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

        if ($activity) {
            Flash::info(Lang::get('dma.friends::lang.app.activityCodeSuccess', ['title' => $activity->title]));
        } else {
            Flash::error(Lang::get('dma.friends::lang.app.activityCodeError', ['code' => $params['code']]));
        }

        return [
            '#flashMessages'    => $this->renderPartial('@flashMessages'),
            'span.points'       => $user->points,
        ];

    }

}
