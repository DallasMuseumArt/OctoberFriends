<?php namespace DMA\Friends\Components;

use Cms\Classes\ComponentBase;
use Auth;
use Hash;
use Redirect;
use Flash;
use Lang;

class UserTimeout extends ComponentBase
{

    use \System\Traits\ViewMaker;

    public function componentDetails()
    {
        return [
            'name'        => 'User Timeout',
            'description' => 'Automatiically logs a user out after X amount of seconds'
        ];
    }

    public function defineProperties()
    {
        return [
            'title' => [
                'title' => 'Modal Title',
            ],
            'timeout' => [
                'title'     => 'Timeout in Seconds',
                'default'   => 30,
            ],
 
        ];
    }

    public function onRender()
    {

        $this->addAssets();

        $this->page['timeout'] = $this->property('timeout');
    }

    public function onRenderModal()
    {
        return $this->renderPartial('@modalDisplay', [
            'title'     => $this->property('title'),
            'content'   => $this->renderPartial('@timeout-modal'),
        ]);

    }

    public function onStayLoggedIn()
    {
        $user = Auth::getUser();
        $password = post('password');

        if (Hash::check($password, $user->password)) {
            return Redirect::to('friends');
        } else {
            Flash::error(Lang::get('dma.friends::lang.user.passwordFail'));
            return ['#flashMessages'    => $this->renderPartial('@flashMessages')];
        }

    }

    public function onLogout()
    {
        Auth::logout();
        return Redirect::to('/');
    }

    public function addAssets()
    {
        $this->addJs('/modules/system/assets/ui/vendor/bootstrap/js/modal.js');
        $this->addJs('/modules/system/assets/ui/js/popup.js');
        $this->addJs('components/usertimeout/assets/user-timeout.js');
        $this->addCss('components/usertimeout/assets/user-timeout.css');
    }
}