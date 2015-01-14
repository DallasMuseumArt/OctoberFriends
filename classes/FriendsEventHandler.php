<?php

namespace DMA\Friends\Classes;

use Log;
use Lang;
use Mail;
use Session;
use RainLab\User\Models\User;
use DMA\Friends\Facades\Postman;    
use DMA\Friends\Classes\LocationManager;
use DMA\Friends\Classes\PrintManager;
use DMA\Friends\Classes\Notifications\IncomingMessage;
use DMA\Friends\Activities\ActivityCode;
use DMA\Friends\Activities\LikeWorkOfArt;
use DMA\Friends\Classes\Notifications\NotificationMessage;

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

    public function onAuthRegister($user)
    {
        // Print the reward if user is at a kiosk
        $location = LocationManager::getLocation();
        if ($location) {
            $printManager = new PrintManager($location, $user);
            $printManager->printIdCard();
        }
    }

    public function onNotificationsReady()
    {

        Postman::listen(['sms', 'regex'=>'/.*/'], function(IncomingMessage $message){

            // Find user using mobile phone
            $phoneUser = $message->getFrom();
            
            // Getting first user match. Assuming that phone is 
            // unique in the database  
            if($user = User::where('phone', $phoneUser)->first()){

                // Get code from message
                $params['code'] = $code = $message->getContent();

                // process Activity code first
                if(!$activity = ActivityCode::process($user, $params)){
                    // Not found activity with that code.
                    // Trying if is a object assession number
                    $activity = LikeWorkOfArt::process($user, $params);
                }
                
                // Send SMS and kiosk notification
                $typeMessage = ($activity) ? 'successful' : 'error';
                $template = 'activity_code_' . $typeMessage; 
                Postman::send($template, function(NotificationMessage $notification) use ($user, $code, $activity){

                     // Reply to same phone number
                     $notification->to($user, $user->name);
                     
                     // Send code and activity just in case we want to use in the template 
                     $notification->addData(['code' => $code, 
                                             'activity' => $activity]);
                     
                     // Determine the content of the message
                     $holder = ( $activity ) ? 'activityMessage' : 'activityError';
                     $message = Session::pull($holder);
                                          
                     $notification->message($message);
                     
                }, ['sms', 'kiosk']);
                
                Log::debug('Incoming SMS', ['user' => $user, 'code' => $code, 'activity' => $activity]);
            }else{
                Postman::send('simple', function(NotificationMessage $notification) use ( $phoneUser ){
                
                    $user = new User();
                    $user->phone = $phoneUser;
                    // Reply to same phone number
                    $notification->to($user, $user->name);
                    $notification->message(Lang::get('dma.friends::lang.user.memberPhoneNotFound'));
                     
                }, ['sms']);                
            }
             
        });        
    }
    
    public function subscribe($events)
    {   
        $events->listen('dma.friends.activity.completed', 'DMA\Friends\Classes\FriendsEventHandler@onActivityCompleted');
        $events->listen('dma.friends.badge.completed', 'DMA\Friends\Classes\FriendsEventHandler@onBadgeCompleted');
        $events->listen('dma.friends.reward.redeemed', 'DMA\Friends\Classes\FriendsEventHandler@onRewardRedeemed');
        $events->listen('dma.friends.step.completed', 'DMA\Friends\Classes\FriendsEventHandler@onStepCompleted');
        $events->listen('auth.login', 'DMA\Friends\Classes\FriendsEventHandler@onAuthLogin');
        $events->listen('auth.register', 'DMA\Friends\Classes\FriendsEventHandler@onAuthRegister');
        
        // Register events for listen incomming data by each channel
        //$events->listen('dma.notifications.ready', 'DMA\Friends\Classes\FriendsEventHandler@onNotificationsReady');
        $this->onNotificationsReady();
    }   
}
