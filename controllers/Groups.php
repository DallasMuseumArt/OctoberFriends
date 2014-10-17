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

    protected function execPageAction($actionName, $parameters)
    {
    	\Log::info($actionName);
    	\Log::info($parameters);
    	return parent::execPageAction($actionName, $parameters);
    }
    
    public function update($recordId, $context = null){
    	//throw new \Exception('' . get_object_vars($this));
    	\Log::info(get_class($this));

    	
    	return $this->asExtension('FormController')->update($recordId, $context);
    }
    
	public function update_onSave($recordId, $context = null)
	{
	
	    // Call the FormController behavior update() method
	    return $this->asExtension('FormController')->update_onSave($recordId, $context);
	}   
    
}