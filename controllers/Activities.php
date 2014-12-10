<?php namespace DMA\Friends\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use DMA\Friends\Models\Badge;

/**
 * Activities Back-end Controller
 */
class Activities extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.RelationController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('DMA.Friends', 'friends', 'activities');
    }

    /**
     * Return an array of badges associated with the controller model
     * @return array
     * An array of badge models
     */
    public function getBadges()
    {
        $activity = $this->widget->formDescription->model;

        $badges = Badge::join('dma_friends_steps', 'dma_friends_badges.id', '=', 'dma_friends_steps.badge_id')
            ->select('dma_friends_badges.title', 'dma_friends_badges.id')
            ->where('dma_friends_steps.activity_id', $activity->id)->get();
        return $badges;
    }
}
