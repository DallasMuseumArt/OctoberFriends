<?php namespace DMA\Friends\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Steps Back-end Controller
 */
class Groups extends Controller
{
    public $implement = [
    	'Backend.Behaviors.ListController',    
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.RelationController',
    ];
   
    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relations.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('DMA.Friends', 'friends', 'groups');
    }
    
 
    public function relationExtendQuery($query, $field, $manageMode=null)
    {
        # Extend relation query to exclude owner of the group
        $ownerId = $this->relationObject->getParent()->owner_id;
        $foreignKeyName = $this->relationModel->getQualifiedKeyName();        
        $query->where($foreignKeyName, '<>', $ownerId);

        
    }
    
    
    public function update_onSave($recordId = null, $context = null)
    {
   
        // parent::update_onSave($context) don't work here because 
        // this method is injected by FormController behavior so
        // the following code manually get FormController instance and 
        // and calll create_onSave method

        if ($extension = @$this->extensionData['methods']['update_onSave']) {
            $extensionObject = $this->extensionData['extensions'][$extension];

            $model = $this->formFindModelObject($recordId);
            $noCode = empty($model->code);
            
            if (method_exists($extension, 'update_onSave') && is_callable([$extension, 'update_onSave']))
                 $return = call_user_func_array(array($extensionObject, 'update_onSave'), [$recordId, $context]);
            
            if(!$return && $noCode){
                // We need to get again this object 
                $model = $this->formFindModelObject($recordId);
                return [
                       '#group_code' => $model->code
                ];
            }else{
                return $return;
            }
        }
          
    }
   
            
}

