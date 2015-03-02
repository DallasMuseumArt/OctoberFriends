<?php

namespace DMA\Friends\FormWidgets;

use Backend\Classes\FormWidgetBase;

/**
 * Time Restriction Widget
 * 
 * This widget provides form elements for managing 
 * complex time restriction requirements
 * 
 * @package dma\friends
 * @author Kristen Arnold
 */
class TimeRestrictions extends FormWidgetBase
{
    /** 
     * {@inheritDoc}
     */
    public function widgetDetails()
    {
        return [
            'name'        => 'Time Restrictions',
            'description' => 'form widget for displaying time restriction options'
        ];
    }

    /** 
     * {@inheritDoc}
     */
    public function render()
    {
        $this->prepareVars();
        return $this->makePartial('widget');
    }

    public function prepareVars()
    {
        $data = $this->getLoadValue();

        $this->vars['name']         = $this->formField->getName();
        $this->vars['start_time']   = $data['start_time'];
        $this->vars['end_time']     = $data['end_time'];

        if (isset($data['days'])) {
            foreach($data['days'] as $day => $val) {
                $this->vars['days'][$day-1] = $val;
            }
        } else {
            $this->vars['days'] = [1, 2, 3, 4, 5, 6, 7];
        }
    }

    /** 
     * {@inheritDoc}
     */
    public function getSaveValue($value)
    {   
        // Convert check values to conform to ISO php day standards
        for ($i = 0; $i < 7; $i++) {
            $days[$i+1] = isset($value['days'][$i]) ? true : false;
        }

        $value['days'] = $days;

        return $value;
    }  

    /** 
     * {@inheritDoc}
     */
    public function loadAssets()
    {   
        $this->addJs('jquery.plugin.min.js');
        $this->addJs('jquery.timeentry.min.js');
        $this->addJs('time.restriction.js');
        $this->addCss('jquery.timeentry.css');
        $this->addCss('time.restriction.css');
    }  
}
