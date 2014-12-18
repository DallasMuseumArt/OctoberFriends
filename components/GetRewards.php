<?php namespace DMA\Friends\Components;

use Cms\Classes\ComponentBase;
use DMA\Friends\Models\Reward;
use DMA\Friends\Classes\RewardManager;
use Auth;
use View;
use Session;
use Flash;

class GetRewards extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Get Rewards',
            'description' => 'Provide a listing of available rewards'
        ];
    }

    public function defineProperties()
    {
        return [
            'limit' => [
                'title'     => 'Limit',
                'default'   => 8,
            ],
        ];
    }

    public function onRun()
    {

        $renderedRewards = [];
        //TODO determine how to sort rewards
        $rewards = Reward::isActive()->orderBy('points')->paginate($this->property('limit'));

        foreach($rewards as $reward) {
            $renderedRewards[] = [
                'rendered' => View::make('dma.friends::rewardPreview', ['reward' => $reward])->render(),
                'id' => $reward->id,
            ];
        }
        $this->page['rewards'] = $renderedRewards;
        $this->page['links']   = $rewards->links();

    }

    public function onRedeem()
    {
        $id = post('id');
        $user = Auth::getUser();
        RewardManager::redeem($id, $user);

        $message = Session::pull('rewardMessage');

        if ($message) {
            //TODO replace with advanced notification system when ready
            Flash::info($message);
        } else {
            Flash::error(Session::pull('rewardError'));
        }

        return [
            '#flashMessages'    => $this->renderPartial('@flashMessages'),
            'span.points'       => $user->points,
        ];
    }

}