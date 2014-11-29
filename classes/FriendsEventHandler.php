<?php

namespace DMA\Friends\Classes;

/**
 * Manage custom events in the friends platform
 *
 * @package DMA\Friends\Classes
 * @author Kristen Arnold, Carlos Arroyo
 */
class FriendsEventHandler {

    public function onActivityCompleted($event)
    {   
    }

    public function onBadgeEarned($event)
    {
        //TODO verify
        Mail::send('dma.friends::mail.badge', $data, function($message) use ($user)
        {
            $message->to($user->email, $user->full_name);
        });
    }

    public function onRewardRedeemed($event)
    {   
        // TODO
        // Send email to user and admin if required
        Mail::send('dma.friends::mail.reward', $data, function($message) use ($user)
        {
            $message->to($user->email, $user->full_name);
        });
    }   

    public function onStepCompleted($event)
    {   
        // TODO: load badges with step and see if badge has been completed
    }   

    public function onAuthLogin($event)
    {   
        // Log an event that the user has logged in
    }   

    public function subscribe($events)
    {   
        $events->listen('friends.activityCompleted', 'DMA\Friends\Classes\FriendsEventHandler@onActivityCompleted');
        $events->listen('friends.badgeEarned', 'DMA\Friends\Classes\FriendsEventHandler@onBadgeEarned');
        $events->listen('friends.rewardRedeemed', 'DMA\Friends\Classes\FriendsEventHandler@onRewardRedeemed');
        $events->listen('friends.stepCompleted', 'DMA\Friends\Classes\FriendsEventHandler@onStepCompleted');
        $events->listen('auth.login', 'DMA\Friends\Classes\FriendsEventHandler@onAuthLogin');
    }   
}
