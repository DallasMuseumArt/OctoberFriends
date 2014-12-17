<?php namespace DMA\Friends\Components;

use Request;
use Auth;
use Redirect; 
use Cms\Classes\ComponentBase;

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
                'title'     => 'Display only unread notifications',
                'type'      => 'checkbox',
                'default'   => true
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
            $query = $user->notifications();
            
            if ($this->property('onlyUnread')){
                $query = $query->unread();   
            }
            
            // sort by creation
            $query = $query->orderBy('created_at','desc');
            
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
        //$this->addCss('components/grouprequest/assets/css/group.request.css');
        //$this->addJs('components/grouprequest/assets/js/group.request.js');
        
        // Populate page user and other variables
    	$this->prepareVars();
    	//$this->markAllAsRead();
    
    }    
   
    

    
  
}