<?php

namespace DMA\Friends\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Rainlab\User\Models\User;
use DMA\Friends\Models\Usermeta;
use DMA\FriendsRE\Models\RazorsEdge;

class EmailOptin extends ReportWidgetBase
{
    public function render()
    {
        $friends = User::count();
        $optin = Usermeta::where('email_optin', 1)->count();
        $notOptin = $friends - $optin;


        $this->vars['optin']     = number_format($optin);
        $this->vars['notOptin']  = number_format($notOptin);
        $this->vars['percent']   = round(($optin / $friends) * 100) . '%';

        return $this->makePartial('widget');
    }

    public function getOptinFriends($friends)
    {
        $numWithOptin = Usermeta::where('email_optin', 1)->count();

        return round($numWithOptin / $friends * 100);
    }
}
