<?php namespace DMA\Friends\Components;

use Cms\Classes\ComponentBase;
use Auth;
use View;
use DMA\Friends\Classes\BadgeManager;
use DMA\Friends\Models\Badge;

class UserBadges extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'User Badges',
            'description' => 'Shows a listing of badges a user has earned'
        ];
    }

    public function defineProperties()
    {
        return [
            'limit' => [
                'title'     => 'Limit',
                'default'   => 10,
            ]
        ];
    }

    public function onRun()
    {
        $user = Auth::getUser();

        if (!$user) return;

        $renderedBadges = [];
        $badges = $user->badges()
            ->take($this->property('limit'))
            ->groupBy('id')
            ->get();
        
        foreach($badges as $badge) {
            $renderedBadges[] = View::make('dma.friends::badgePreview', ['model' => $badge, 'class' => 'col-md-3 col-sm-4'])->render();
        }

        $this->page['badges'] = $renderedBadges;
        
    }

    public function onRenderBadge()
    {
        $id = post('id');
        $badge = Badge::find($id);
        return BadgeManager::render($this, $badge);
    }

}
