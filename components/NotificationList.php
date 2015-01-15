<?php namespace DMA\Friends\Components;

use Request;
use Auth;
use Redirect; 
use Cms\Classes\ComponentBase;
use DMA\Friends\Models\Notification;

use System\Classes\ApplicationException;

class NotificationList extends ComponentBase
{
  
    public function componentDetails()
    {
        return [
            'name'        => 'User Notification List',
            'description' => 'Shows a list of notifications'
        ];
    }
    
    public function defineProperties()
    {
        return [
            'onlyUnread' => [
                    'title'             => 'Show only unread',
                    'description'       => 'Display only unread notifications',
                    'type'              => 'checkbox',
            ],
            'autoMarkAsRead' => [
                    'title'             => 'Auto mark as read',
                    'description'       => 'Mark as read after display for first time',
                    'type'              => 'checkbox',
            ],
            'showLimit' => [
                    'title'             => 'Show maximum',
                    'description'       => 'Show maximum a given number of notifications',
                    'validationPattern' => '^\d+$',
                    'validationMessage' => 'Value should be a positive integer',
                    'default'           => 20
            ]
        ];
    }
    
    protected function getuser()
    {
        $this->user = Auth::getUser();
        return $this->user;
    }

    protected function getNotifications()
    {
        if($user = $this->getUser()){
            // Get query to notifications and expire old notifications
            $query = $user->notifications();
            $query = $query->expire(); // Expire old notifications
            
            if ($this->property('onlyUnread')){
                $query = $query->unread();
            }
            
            // Limit 
            $limit = $this->property('showLimit');
            $unReadCount = $user->notifications()
                                ->unread()
                                ->count();
            
            $limit = ( $limit < $unReadCount ) ? $unReadCount : $limit;
            
            // sort by creation
            $query = $query->orderBy('created_at','desc')
                           ->take($limit);
            
            
            return $query->get()->toArray();
        }else{
            return [];
        }
    }
    
    protected function markAllAsRead(){
        if($user = $this->getUser()){
            // Mark all message as readed
            $user->notifications()->markAllAsRead();
        }
    }
    
    protected function prepareVars($vars = [])
    {
        $user = $this->getUser();
        $this->page['notifications'] = $this->getNotifications();
     
       
        foreach($vars as $key => $value){
            // Append or refresh extra variables
            $this->page[$key] = $value;
        }

                   
    }

    public function onRun()
    {
        // Inject CSS and JS
        $this->addCss('components/notificationlist/assets/css/notification.list.css');
        $this->addJs('components/notificationlist/assets/js/notification.list.js');
        
        // Populate page user and other variables
    	$this->prepareVars();
    	
    	if($this->property('autoMarkAsRead')){
    	   $this->markAllAsRead();
        }
    
    }    
   
    
    ## AJAX
    public function onMarkRead()
    {
        if (($id = post('id')) != ''){
            if($notification = Notification::find($id)){
                $notification->markAsRead();
            }
        }
    }

    
  
}