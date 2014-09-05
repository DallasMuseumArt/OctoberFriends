<?php namespace DMA\Friends\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * ActivityTypes Back-end Controller
 */
class ActivityTypes extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('October.System', 'system', 'settings');
    }
}
