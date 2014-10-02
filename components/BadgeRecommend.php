<?php namespace DMA\Friends\Components;

use Cms\Classes\ComponentBase;
use DMA\Friends\Models\Badge;
use Auth;
use View;

class BadgeRecommend extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Recommended Badges',
            'description' => 'Provide a listing of recommended badges'
        ];
    }

    public function defineProperties()
    {
        return [
            'limit' => [
                'title'     => 'Limit',
                'default'   => 10,
            ],
        ];
    }

    public function onRun()
    {
        //$user = Auth::getUser();
        // use for testing
        $user = \RainLab\User\Models\User::find(1);

        if (!$user) return;

        $renderedBadges = [];
        // TODO: this will need updated to accomodate recommendation engine
        $badges = Badge::notCompleted($user)
            ->take($this->property('limit'))
            ->get();

        foreach($badges as $badge) {
            $renderedBadges[] = View::make('dma.friends::badgePreview', ['badge' => $badge])->render();
        }

        $this->page['badges'] = $renderedBadges;

    }

}
