<?php namespace DMA\Friends\Components;

use Request;
use Auth;
use Lang;
use Redirect; 
use Exception;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use DMA\Friends\Models\Settings;
use DMA\Friends\Models\UserGroup;
use October\Rain\Support\Facades\Flash;
use RainLab\User\Models\Settings as UserSettings;
use RainLab\User\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use DMA\Friends\Traits\MultipleComponents;

class GroupJoinCodeForm extends ComponentBase
{
    
    use MultipleComponents;
    
    private $user;
    const VIEW_GROUPS  = '@groups-list';
    const VIEW_MEMBERS = '@members-list';
    
    /**
     * @var string RainLab.User pluging username field
     */
    private $loginAttr;
     
   
    /**
     * @var string
     */
    private $currentView = null;
    
    
    /**
     * @var integer
     */
    private $currentGroupId = null;
    
    
    
    public function componentDetails()
    {
        return [
            'name'        => 'User group join code form',
            'description' => 'Join current logged user to a group using its code'
        ];
    }
    
    public function defineProperties()
    {
        return [            
                'afterJoinRedirectTo' => [
                        'title'     => 'Redirect to after join group',
                        'type'      => 'dropdown',
                        'default'   =>  null
                ],
                'showMemberOf' => [
                        'title'     => 'Show list of groups this user is member',
                        'type'      => 'checkbox',
                        'default'   =>  true
                ],
                
        ];
    }
    
    protected function getuser()
    {
        $this->user = Auth::getUser();
        return $this->user;
    }

    /**
     * @return DMA\Friends\Models\UserGroup
     */
    protected function getGroups()
    {
        $user = $this->getuser();
        $groups = User::find($user->getKey())
                       ->groups()
                       ->where('is_active', true)
                       ->where(function($query) use ($user){
                            $status = [ 
                                //UserGroup::MEMBERSHIP_PENDING, 
                                UserGroup::MEMBERSHIP_ACCEPTED,
                                //UserGroup::MEMBERSHIP_CANCELLED
                            ];
                            $query->whereIn('membership_status', $status);
                       })->orderBy('membership_status', 'DESC')
                         ->orderBy('name', 'ASC')
                         ->get();
         return $groups;
    }
    
    
    protected function getGroup($id)
    {
        $user = $this->getUser();
        if (!is_null($user)){
            return UserGroup::find($id)
            ->where('is_active', true)
            ->first();
        }
    }
    
    protected function getLoginAttr()
    {
        if (is_null($this->loginAttr)){
            $this->loginAttr = UserSettings::get('login_attribute', UserSettings::LOGIN_EMAIL);
        }
        return $this->loginAttr;
    }
    
    
    protected function prepareVars($vars = [])
    {

        $this->currentView = (is_null($this->currentView)) ? self::VIEW_GROUPS : $this->currentView;
        $vars['using_code'] = ( $this->using_code() ) ? 'code' : 'name';
        
        if($this->property('showMemberOf', false)) {
 
            switch($this->currentView) {
                case self::VIEW_GROUPS:
                    // Refresh List of groups
                    $vars['member_of']  = $this->getGroups(); 
                    break;
            
                case self::VIEW_MEMBERS:
                    // if currentGroupId is null try to get it from POST variables
                    $groupId = $this->currentGroupId;
                    $groupId = ( empty($groupId) ) ? post('groupId') : $groupId;
            
                    // Group
                    $group = $this->getGroup($groupId);
                    $this->page['group'] = $group;
            
                    // Members
                    $this->page['group_members'] = (!is_null($group)) ? UserGroup::find($groupId)->getUsers() : new Collection([]);
            
                    // Get login attribute configured in RainLab.User plugin
                    $this->page['loginAttr'] = $this->getLoginAttr();
            
                    break;
            
            }
        }
        
        $vars['view'] = $this->currentView;
        
        
        
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

    	// Populate users and other variables
    	$this->prepareVars();
    }    
   
    protected function using_code(){
        return !Settings::get('use_group_name_as_code', false);
    }
    
    
    /**
     * Ajax handler for accept request
     */
    public function onJoinGroup(){

        if (($code = post('code')) != ''){
            $user = $this->getuser();

            try {
                
                if ($this->using_code()){
                    UserGroup::joinByCode($code, $user);
                }else{
                    UserGroup::joinByGroupName($code, $user);
                }

                $message = Lang::get('dma.friends::lang.groups.welcomeToGroup');
                Flash::error($message);
                
                if ($goTo = $this->property('afterJoinRedirectTo')){
                    return Redirect::to($goTo);
                }
                
            }catch (Exception $e){
                $message = $e->getMessage();
                if ($e instanceof ModelNotFoundException) {
                    $message = Lang::get('dma.friends::lang.exceptions.groupNotFound');
                }
                Flash::error($message);                
            }
              
        }
        
        // Updated list of request and other vars
        $this->prepareVars();
    }
  
    
    public function onLeaveGroup(){
        try{
            if (($groupId = post('groupId')) != ''){
                $group = UserGroup::findOrFail($groupId);
                $user = $this->getuser();
                $group->rejectMembership($user);
            }
        }catch (Exception $e){
            $message = $e->getMessage();
            if ($e instanceof ModelNotFoundException) {
                $message = Lang::get('dma.friends::lang.exceptions.groupNotFound');
            }
            Flash::error($message);
        }
    }
    
    /**
     * Ajax handler to access membership group tools
     */
    public function onGoToView(){
        if (($view = post('view')) != ''){
    
            $this->currentView    = $view;
            $this->currentGroupId = post('groupId');
    
            $this->prepareVars();
    
            return [
                    "#view" => $this->renderPartial($view)
            ];
        }
    }
    
    
    ###
    # OPTIONS
    ##
    
    private function getListPages()
    {
    	$pages = Page::sortBy('baseFileName')->lists('baseFileName', 'url');
    	return [''=>'- none -'] + $pages;
    }
    
    
    public function getAfterJoinRedirectToOptions()
    {
    	return $this->getListPages();
    }    
  
}