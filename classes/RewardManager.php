<?php

namespace DMA\Friends\Classes;

use DMA\Friends\Models\Reward;
use DMA\Friends\Classes\UserExtend;
use Exception;
use Lang;
use Event;
use Session;

class RewardManager
{
    /**
     * Redeem a reward for a user
     * @param int $id
     * The id of the reward to redeem
     * @param User $user
     * The user model to redeem the reward for
     */
    public static function redeem($id, $user)
    {
        $reward = Reward::find($id);

        if (!$reward) {
            throw Exception(Lang::get('dma.friends.exceptions.missingReward', ['id' => $id]));
        }

        try {
            $user->rewards()->save($reward);
            $userExtend = new UserExtend($user);
            $userExtend->removePoints($reward->points);

            Event::fire('dma.friends.reward.redeemed', [$reward, $user]);
            // TODO handle printing of reward coupon

            Session::put('rewardMessage', Lang::get('dma.friends::lang.rewards.redeemed', ['title' => $reward->title]));
        } catch (Exception $e) {
            throw Exception(Lang::get('dma.friends.exceptions.rewardFailed'));
        }
    }
}