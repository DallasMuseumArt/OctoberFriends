<?php

namespace DMA\Friends\Classes;

use Mail;
use DMA\Friends\Facades\Postman;    
use DMA\Friends\Classes\LocationManager;
use DMA\Friends\Classes\PrintManager;
use DMA\Friends\Classes\Notifications\IncomingMessage;

/**
 * Manage custom events in the friends platform
 *
 * @package DMA\Friends\Classes
 * @author Kristen Arnold, Carlos Arroyo
 */
class FriendsEventHandler {

    /**
     * Handle the dma.friends.activity.completed event
     * @param Activity $activity
     * The activity model that has just been completed
     * @param User $user
     * The user that completed the activity
     */
    public function onActivityCompleted($activity, $user)
    {   
    }

    /**
     * Handle the dma.friends.badge.completed event
     * @param Badge $badge
     * The badge model that has just been completed
     * @param User $user
     * The user that completed the badge
     */
    public function onBadgeCompleted($badge, $user)
    {
        $data = [
            'badge' => $badge,
            'user'  => $user,
        ];

        Mail::send('dma.friends::mail.badge', $data, function($message) use ($user)
        {
            \Debugbar::info('badge trigger fired');
            $message->to($user->email, $user->full_name);
        });
    }

    /**
     * Handle the dma.friends.reward.redeemed event
     * @param Reward $reward
     * The reward model that has just been redeemed
     * @param User $user
     * The user that redeemed the reward
     */
    public function onRewardRedeemed($reward, $user)
    {   
        $data = [
            'reward'    => $reward,
            'user'      => $user,
        ];

        Mail::send('dma.friends::mail.reward', $data, function($message) use ($user)
        {
            $message->to($user->email, $user->full_name);
        });

        // Print the reward if user is at a kiosk
        $location = LocationManager::getLocation();
        if ($location) {
            $printManager = new PrintManager($location, $user);
            $printManager->printCoupon($reward);
        }

    }   

    /**
     * Handle the dma.friends.step.completed event
     * @param Step $step
     * The step model that has just been completed
     * @param User $user
     * The user that completed the step
     */
    public function onStepCompleted($step, $user)
    {   
        
    }   

    public function onAuthLogin($event)
    {   
        // Log an event that the user has logged in
    }

    public function onNotificationsReady()
    {

        Postman::listen(['sms', 'regex' => '/(\d.*)\.(\d.*)/'], function(IncomingMessage $message){
            //var_dump($message);
            //var_dump($message->getMatches());
            \Log::info($message->getContent());
            \Log::info($message->getMatches());
        });        
    }
    
    public function subscribe($events)
    {   
        $events->listen('dma.friends.activity.completed', 'DMA\Friends\Classes\FriendsEventHandler@onActivityCompleted');
        $events->listen('dma.friends.badge.completed', 'DMA\Friends\Classes\FriendsEventHandler@onBadgeCompleted');
        $events->listen('dma.friends.reward.redeemed', 'DMA\Friends\Classes\FriendsEventHandler@onRewardRedeemed');
        $events->listen('dma.friends.step.completed', 'DMA\Friends\Classes\FriendsEventHandler@onStepCompleted');
        $events->listen('auth.login', 'DMA\Friends\Classes\FriendsEventHandler@onAuthLogin');
        
        // Register events for listen incomming data by each channel
        //$events->listen('dma.notifications.ready', 'DMA\Friends\Classes\FriendsEventHandler@onNotificationsReady');
        $this->onNotificationsReady();
    }   
}
