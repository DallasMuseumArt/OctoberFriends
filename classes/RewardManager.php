<?php

namespace DMA\Friends\Classes;

use DMA\Friends\Models\Reward;
use DMA\Friends\Classes\UserExtend;
use SystemException;
use Lang;
use Auth;
use View;
use Event;
use Session;
use FriendsLog;

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
            throw SystemException(Lang::get('dma.friends.exceptions.missingReward', ['id' => $id]));
        }

        try {

            if ($reward->inventory !== null && $reward->inventory == 0) {
                Session::put('rewardError', Lang::get('dma.friends::lang.rewards.noInventory'));
                return;
            }

            $userExtend = new UserExtend($user);

            if ($userExtend->removePoints($reward->points, false)) {

                if ($reward->inventory > 0) {
                    $reward->inventory--;
                    $reward->save();
                }
                
                $user->rewards()->save($reward);
                
                Event::fire('dma.friends.reward.redeemed', [$reward, $user]);

                $params = [
                    'user'      => $user,
                    'object'    => $reward,
                ];

                FriendsLog::reward($params);
                // TODO handle printing of reward coupon

                Session::put('rewardMessage', Lang::get('dma.friends::lang.rewards.redeemed', ['title' => $reward->title]));
            } else {
                Session::put('rewardError', Lang::get('dma.friends::lang.rewards.noPoints'));
            }
        } catch (Exception $e) {
            throw SystemException(Lang::get('dma.friends.exceptions.rewardFailed'));
        }
    }

    public static function render($controller, $reward)
    {

        $user = Auth::getUser();
        
        return $controller->renderPartial('@modalDisplay', [
            'title'     => $reward->title,
            'content'   => View::make('dma.friends::reward', ['model' => $reward, 'user' => $user])->render(),
        ]);
    }
}
