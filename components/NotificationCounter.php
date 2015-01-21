<?php namespace DMA\Friends\Components;

use Request;
use Auth;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;

use System\Classes\ApplicationException;

class NotificationCounter extends ComponentBase
{
  
    public function componentDetails()
    {
        return [
            'name'        => 'User Notification Counter',
            'description' => 'Shows number of unread notification of the current logged user'
        ];
    }
    
    public function defineProperties()
    {
        return [
            'goToNotificationPage' => [
                    'title'     => 'Link to notification page',
                    'type'      => 'dropdown',
                    'default'   => '/'
            ],
            'cssClass' => [
                    'title'     => 'CSS classes',
                    'type'      => 'dropdown',
                    'default'   => 'nav-item notifications-feed icon-envelope'
            ]                    
        ];
    }
    
    protected function getuser()
    {
        $this->user = Auth::getUser();
        return $this->user;
    }

    
    protected function prepareVars($vars = [])
    {
        $user = $this->getUser();
        $count = (is_null($user)) ? 0 : $user->notifications()->unread()->count();
        $this->page['count'] = $count;
        $this->page['goToPage'] = $this->property('goToNotificationPage');
        $this->page['cssClass'] = $this->property('cssClass');
     
       
        foreach($vars as $key => $value){
            // Append or refresh extra variables
            $this->page[$key] = $value;
        }

                   
    }

    public function onRun()
    {
        // Inject CSS and JS
        //$this->addCss('components/notificationcounter/assets/css/notification.counter.css');
        $this->addJs('components/notificationcounter/assets/js/notification.counter.js');
        
        // Populate page user and other variables
    	$this->prepareVars();
    
    }    
   
    ###
    # OPTIONS
    ##
    
    public function getGoToNotificationPageOptions()
    {
        $pages = Page::sortBy('baseFileName')->lists('baseFileName', 'url');
    	return [''=>'- none -'] + $pages;
    }   

    ## AJAX
    public function onUpdateCounter()
    {
        //$this->prepareVars();
        //return $this->page;
        return [];
    }
  
}