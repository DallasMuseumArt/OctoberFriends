<?php

namespace DMA\Friends\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Rainlab\User\Models\User;
use DMA\Friends\Models\Usermeta;

class FriendsMembers extends ReportWidgetBase
{
    public function render()
    {
        $friends = User::count();
        $partners = Usermeta::select('id')
            ->where('current_member', Usermeta::IS_MEMBER)
            ->count();

        $notPartners = $friends - $partners;

        $this->vars['totalFriends'] = number_format($friends);
        $this->vars['notPartners'] = number_format($notPartners);
        $this->vars['partners'] = number_format($partners);
        $this->vars['partnerPercent'] = round(($partners / $friends) * 100) . '%';

        return $this->makePartial('widget');
    }
}
