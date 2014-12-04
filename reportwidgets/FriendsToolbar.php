<?php

namespace DMA\Friends\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use Rainlab\User\Models\User;

class FriendsToolbar extends ReportWidgetBase
{
    public $defaultAlias = 'friendsToolbar';

    /**
     * {@inheritDoc}
     */
    public function widgetDetails()
    {
        return [
            'name'        => 'Friends Toolbar',
            'description' => 'Show some basic statistics on friends'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function render()
    {
        $today = date('Y-m-d');
        $thisWeek = date('Y-m-d', strtotime('last monday'));

        $this->addCss('css/friendstoolbar.css');

        $this->vars['numFriends'] = number_format(User::count());
        $this->vars['todayFriends'] = number_format(User::where('created_at', '>=', $today)->count());
        $this->vars['weekFriends'] = number_format(User::where('created_at', '>=', $thisWeek)->count());

        return $this->makePartial('widget');
    }
}
