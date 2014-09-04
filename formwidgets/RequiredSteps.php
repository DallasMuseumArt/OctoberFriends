<?php

namespace DMA\Friends\FormWidgets;

use Backend\Classes\FormWidgetBase;
use DMA\Friends\Models\Step;

class RequiredSteps extends FormWidgetBase
{
    public function widgetDetails()
    {
        return [
            'name'        => 'Required Steps',
            'description' => 'Associates steps with a badge'
        ];
    }

    public function render()
    {
$debugbar = \App::make('debugbar');
        $this->vars['steps'] = $this->model->steps()->get();
$debugbar->info($this->vars['steps']);

        return $this->makePartial('requiredSteps');
    }
}
