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
    
    }    
   
    ###
    # OPTIONS
    ##
    
    public function getGoToNotificationPageOptions()
    {
        $pages = Page::sortBy('baseFileName')->lists('baseFileName', 'url');
    	return [''=>'- none -'] + $pages;
    }   

    
  
}