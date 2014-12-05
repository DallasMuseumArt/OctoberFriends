<?php namespace DMA\Friends\Components;

use Cms\Classes\ComponentBase;
use DMA\Friends\Models\Reward;
use Auth;
use View;

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
        $rewards = Reward::paginate($this->property('limit'));

        foreach($rewards as $reward) {
            $renderedRewards[] = View::make('dma.friends::rewardPreview', ['reward' => $reward])->render();
        }
        $this->page['rewards'] = $renderedRewards;
        $this->page['links']   = $rewards->links();

    }

}