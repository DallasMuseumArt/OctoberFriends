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

    public function getBadges()
    {
        $activity = $this->widget->formDescription->model;

        $badges = Badge::with(['steps' => function($q) use ($activity) {
            \Debugbar::info($q);
            $q->join('dma_friends_step_badge', 'dma_friends_steps.id', '=', 'dma_friends_step_badge.step_id');
            $q->join('dma_friends_activity_step', 'dma_friends_step_badge.step_id', '=', 'dma_friends_activity_step.step_id');
            $q->where('dma_friends_activity_step.activity_id', $activity->id);
        }])->get();
\Debugbar::info($badges);
        return $badges;
    }
}
