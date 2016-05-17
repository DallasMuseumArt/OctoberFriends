<?php namespace DMA\Friends\Components;

use Auth;
use Lang;
use Request;
use Redirect; 
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use DMA\Friends\Models\Settings;
use DMA\Friends\Models\UserGroup;
use Rainlab\User\Models\User;
use RainLab\User\Models\Settings as UserSettings;
use October\Rain\Support\Facades\Flash;
use Illuminate\Support\Collection;
use DMA\Friends\Traits\MultipleComponents;

class GroupManager extends ComponentBase
{
 
    use MultipleComponents;
    
    const STEP_GROUPS  = '@groups-manager';
    const STEP_MEMBERS = '@members-manager';
    
    
    /**
     * @var string RainLab.User pluging username field
     */
    private $loginAttr;
       
    /**
     * @var RainLab\User\Models\User
     */
    private $user = null;
    
    /**
     * @var string 
     */
    private $currentStep = null;
    

    /**
     * @var integer
     */
    private $currentGroupId = null;
    
    
    public function componentDetails()
    {
        return [
            'name'        => 'Group management',
            'description' => ''
        ];
    }
    
    public function defineProperties()
    {
        return [
                'autoAcceptNewUsers' => [
                        'title'     => 'Auto accept group invitations',
                        'type'      => 'checkbox',
                        'default'   =>  false
                ],
        ];
    }
    
    /**
     * @return bool
     */
    protected function using_code(){
        return !Settings::get('use_group_name_as_code', false);
    }
    
    /**
     * @return RainLab\User\Models\User
     */
    protected function getUser()
    {
        if(is_null($this->user)){
            $this->user = Auth::getUser();
        }
        return $this->user;
    }
    
    /**
     * @return DMA\Friends\Models\UserGroup
     */
    protected function getGroups()
    {
        if($user = $this->getUser()){
            $groups = UserGroup::where('owner_id', $user->getKey())
                                ->isActive()->get();
            return $groups;   
        }
        return [];
    }
    
  
    protected function getGroup($id)
    {
        // Current authenticate user must be the owner
        // of the group
        
        $user = $this->getUser();
        if (!is_null($user)){
            return UserGroup::where('owner_id', $user->getKey())
                    ->where($user->getKeyName(), $id)
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
        
        $this->currentStep = (is_null($this->currentStep)) ? self::STEP_GROUPS : $this->currentStep;

        // Show code ?
        $this->page['use_code'] = $this->using_code();
        
        switch($this->currentStep) {
            case self::STEP_GROUPS:
                // Refresh List of groups
                $this->page['groups'] = $this->getGroups();
                
                break;
                
            case self::STEP_MEMBERS:
                // if currentGroupId is null try to get it from POST variables
                $groupId = $this->currentGroupId;
                $groupId = ( empty($groupId) ) ? post('groupId') : $groupId;
                
                // Group
                $group = $this->getGroup($groupId);
                $this->page['group'] = $group;
                
                // Members
                $this->page['members'] = (!is_null($group)) ? $group->getUsers() : new Collection([]);
                

                // Get login attribute configured in RainLab.User plugin
                $this->page['loginAttr'] = $this->getLoginAttr();
                
                break;
            
        }
        
        $vars['step'] = $this->currentStep;
                
        foreach($vars as $key => $value){
            // Append or refresh extra variables
            $this->page[$key] = $value;
        }

                   
    }

    public function onRun()
    {
        // Inject CSS and JS
        $this->addCss('components/groupmanager/assets/css/group.manager.css');
        $this->addJs('components/groupmanager/assets/js/group.manager.js');
        
        if($user = $this->getUser()){ 
        
            // Populate users and other variables
    	   $this->prepareVars();
    	   
        }
    }    
        
    /**
     * Common Ajax handler for forms using form_ajax tag.
     */
    public function onGroupAction(){
        if( !empty( $step = post('step'))){
            $this->currentStep = $step;
            
            switch ($step){
                case self::STEP_GROUPS:
                    $this->onCreateGroup();
                    break;
                case self::STEP_MEMBERS;
                    $this->onAddMember();
                    break;  
            }
            
        }

    }
    
    /**
     * Ajax handler for creating groups
     */
    public function onCreateGroup()
    {
        // refresh current step
        $this->currentStep = self::STEP_GROUPS;
        
        if( $user = $this->getUser() ){
            if( !empty( $name = post('name'))){
                try{
                    $group = UserGroup::createGroup($user, $name);
                }catch(\Exception $e){
                    Flash::error($e->getMessage());
                }
            }
        }
        
        // Updated list of grous and other vars
        $this->prepareVars();
    }
    
    
    /**
     * Ajax handler for closing groups
     */
    public function onCancelGroup(){
        // refresh current step
        $this->currentStep = self::STEP_GROUPS;
        
    	if (($groupId = post('groupId')) != ''){
    		if($group = UserGroup::find($groupId)){
    			// mark as inactive  group
    			$group->is_active = false;
    			$group->save();
    
                // Updated page variables
                $this->prepareVars();
                
                $message = Lang::get('dma.friends::lang.groups.groupCanceled');
                Flash::info($message);
                
    		}else{
    		    $message = Lang::get('dma.friends::lang.exceptions.groupNotFound');
    		    Flash::error($message);
    		}
    	}
    }
    
    /**
     * Ajax handler for adding members
     */
    public function onAddMember(){
        
        // refresh current step
        $this->currentStep = self::STEP_MEMBERS;
        
        if (($groupId = post('groupId')) != ''){
        
            // Add to group
            $group = $this->getGroup($groupId);
    
            if (($username = post('username')) != ''){
                $user = User::where($this->getLoginAttr(), '=', $username)->first();
                if ($user){
                    \Log::info(UserGroup::getActiveMembershipsCount($user));
                    try{
                        $added = $group->addUser($user);
                        if($added){
                            if ($this->property('autoAcceptNewUsers')){
                                $group->acceptMembership($user);
                            }
                        }else{
                            if($user->getKey() == $group->owner->getKey()) {
                                $message = Lang::get('dma.friends::lang.groups.ownerCanBeMember');
                                Flash::error($message);
                            }
                        }
                    }catch(\Exception $e){
                        Flash::error($e->getMessage());
                    }
                }else{
                    $message = Lang::get('dma.friends::lang.exceptions.userNotFound');
                    Flash::error($message);
                }
        
            }
        
            // Updated list of users and other vars
            $this->prepareVars($group);
        }
    }    
    
    
    /**
     * Ajax handler for removing members
     */
    public function onRemoveMember(){
        // refresh current step
        $this->currentStep = self::STEP_MEMBERS;
        
        if ( (($memberId = post('memberId')) != '') &&
             (($groupId  = post('groupId'))  != '') ) {
                 
            $user = User::find($memberId);
            if ($user){
                if($group = $this->getGroup($groupId)) {
                    // remove from group
                    $group->removeUser($user);
                    
                    // Refresh current groupid
                    $this->currentGroupId = $groupId;
                    
                    // Updated list of users and other vars
                    $this->prepareVars();
                    
                    $message = Lang::get('dma.friends::lang.groups.memberRemoved');
                    Flash::info($message);
                }
    
            }else{
                $message = Lang::get('dma.friends::lang.exceptions.userNotFound');
                Flash::info($message);
            }
        }
    }
    
    
    /**
     * Ajax handler to access membership group tools
     */
    public function onGoToStep(){
        if (($step = post('step')) != ''){

            $this->currentStep    = $step;
            $this->currentGroupId = post('groupId');
            
            $this->prepareVars();

            return [
                "#step" => $this->renderPartial($step)
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

}