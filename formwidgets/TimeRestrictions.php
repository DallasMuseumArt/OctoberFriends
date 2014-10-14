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
        return $this->makePartial('widget');
    }

    public function prepareVars()
    {
        $data = $this->getLoadData();
        $this->vars['start_time'] = $data['start_time'];
        $this->vars['end_time'] = $data['end_time'];

        if (isset($data['days'])) {
            $this->vars['days'] = $data['days'];
        } else {
            $this->vars['days'] = [1, 2, 3, 4, 5, 6, 7];
        }
    }
}
