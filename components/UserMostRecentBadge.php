<?php namespace DMA\Friends\Components;

use Cms\Classes\ComponentBase;
use Auth;
use View;
use Lang;

class UserMostRecentBadge extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Most Recent User Badge',
            'description' => 'Shows the most recent badge a user has earned'
        ];
    }

    public function onRun()
    {
        $user = Auth::getUser();

        if (!$user) return;

        $badge = $user->badges()->first();

        if (!$badge) {
            $this->page['badge'] = Lang::get('dma.friends::lang.badges.noBadges');
            return;
        }

        $this->page['badge'] = View::make('dma.friends::badgePreview', ['model' => $badge])->render();
        
    }
}
