<?php namespace DMA\Friends\Components;

use Auth;
use Flash;
use Lang;
use Cms\Classes\ComponentBase;
use ActivityCode;

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

        $activity = ActivityCode::process($user, $params);

        if ($activity) {
            Flash::info(Lang::get('dma.friends::lang.app.activityCodeSuccess', ['name' => $activity->title]));
        } else {
            Flash::error(Lang::get('dma.friends::lang.app.activityCodeError', ['code' => $params['code']]));
        }

        return [
            '#flashMessages' => $this->renderPartial('@flashMessages')
        ];

    }

}
