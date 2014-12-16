<?php

namespace DMA\Friends\FormWidgets;

use Backend\Classes\FormWidgetBase;
use DMA\Friends\Classes\UserExtend;


/**
 * Time Restriction Widget
 * 
 * This widget provides form elements for managing 
 * complex time restriction requirements
 * 
 * @package dma\friends
 * @author Kristen Arnold
 */
class UserPoints extends FormWidgetBase
{
    /** 
     * {@inheritDoc}
     */
    public function widgetDetails()
    {
        return [
            'name'        => 'User Points',
            'description' => 'form widget for points options'
        ];
    }

    /** 
     * {@inheritDoc}
     */
    public function render()
    {
        return $this->makePartial('widget');
    }

    public function onAdd()
    {
        $points = post('points');

        if (!is_numeric($points)) return;

        $userExtend = new UserExtend($this->model);
        $userExtend->addPoints($points);

        return [
            'span.points'       => $userExtend->user->points,
            'span.points-today' => $userExtend->user->points_today,
            'span.points-week'  => $userExtend->user->points_this_week,
        ];
    }

    public function onRemove()
    {
        $points = post('points');

        if (!is_numeric($points)) return;

        $userExtend = new UserExtend($this->model);
        $userExtend->removePoints($points);

        return [
            'span.points'       => $userExtend->user->points,
            'span.points-today' => $userExtend->user->points_today,
            'span.points-week'  => $userExtend->user->points_this_week,
        ];
    }

    /** 
     * {@inheritDoc}
     */
    public function loadAssets()
    {   
        $this->addCss('user-points.css');
    } 
}