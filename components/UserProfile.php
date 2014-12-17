<?php namespace DMA\Friends\Components;

use Cms\Classes\ComponentBase;
use Auth;
use RainLab\User\Models\State;

class UserProfile extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'User profile form',
            'description' => ''
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $user = Auth::getUser();

        if (!$user) return;

        $this->page['states'] = State::all();
        $this->page['user'] = $user;
    }
}