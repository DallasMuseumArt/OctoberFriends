<?php namespace DMA\Friends\Components;

use Auth;
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
        
        $user = Auth:getUser(); 

        ActivityCode::process($user, $params);
    }
}
