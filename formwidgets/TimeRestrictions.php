<?php

namespace DMA\Friends\FormWidgets;

use Backend\Classes\FormWidgetBase;

class TimeRestrictions extends FormWidgetBase
{
    public function widgetDetails()
    {
        return [
            'name'        => 'Time Restrictions',
            'description' => 'form widget for displaying time restriction options'
        ];
    }

    public function render()
    {
        $this->prepareVars();
$d = \App::make('debugbar');
$d->info($this->vars);
        return $this->makePartial('widget');
    }

    public function prepareVars()
    {
        $data = $this->getLoadData();
        $this->vars['start_time'] = $data['start_time'];
        $this->vars['end_time'] = $data['end_time'];
        $this->vars['days'] = $data['days'];
    }
}
