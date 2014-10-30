<?php

namespace DMA\Friends\FormWidgets;

use Backend\Classes\FormWidgetBase;
use DMA\Friends\Classes\ActivityManager;
use DMA\Friends\Classes\ActivityForm;

/**
 * Activity Type Widget
 * 
 * This widget provides form elements for managing 
 * custom Activities implemented by friends and 3rd party plugins 
 * 
 * @package dma\friends
 * @author Kristen Arnold
 */
class ActivityType extends FormWidgetBase
{
    public $previewMode = false;

        /**
     * @var string If the field element names should be contained in an array.
     * Eg: <input name="nameArray[fieldName]" />
     */
    public $arrayName = true;

    public function __construct($controller, $model, $formField, $configuration = [])
    {
        $this->manager = ActivityManager::instance();

        parent::__construct($controller, $model, $formField, $configuration);
    }

    /** 
     * {@inheritDoc}
     */
    public function widgetDetails()
    {
        return [
            'name'        => 'Activity Type',
            'description' => 'form widget to allow selection of activity types'
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

    public function onChange()
    {
        $name = $this->formField->getName();
        $value = post($this->formField->getName());

        if (!$value) return ['.activity-type .additional-fields' => ''];

        $additionalFields = $this->prepareFormFields($value);

        return ['.activity-type .additional-fields' => $additionalFields];
    }

    /** 
     * {@inheritDoc}
     */
    public function prepareVars()
    {
        $activities = $this->manager->listActivities();

        // Load available activity types
        foreach($activities as $alias => $activity) {
            $activity = new $activity;
            $details  = $activity->details();

            $options[$alias] = $details['name'];
        }

        $this->vars['defaultValue']     = $this->getLoadData();
        $this->vars['additionalFields'] = ($this->vars['defaultValue']) ? $this->prepareFormFields($this->vars['defaultValue']) : null;
        $this->vars['options']          = $options;
        $this->vars['name']             = $this->formField->getName();
    }

    /**
     * Prepare any additional form fields that are configured
     * @param string The activity name to prepare custom fields for
     */
    public function prepareFormFields($value)
    {
        $value = (object)$value;
        \Debugbar::info($value);
        $form = new ActivityForm($this->manager, $value, $this->formField->getName());
        return $form->render();
    }

        /**
     * Process the postback data for this widget.
     * @param $value The existing value for this widget.
     * @return string The new value for this widget.
     */
    public function getSaveData($value)
    {
        $this->manager->saveData($this->model, $value);

        return $value['activity_type'];
    }
}
