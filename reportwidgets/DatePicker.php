<?php

namespace DMA\Friends\ReportWidgets;

use Backend\Classes\ReportWidgetBase;

class DatePicker extends ReportWidgetBase
{
    public $defaultAlias = 'DatePicker';

    /**
     * {@inheritDoc}
     */
    public function widgetDetails()
    {   
        return [
            'name'        => 'Date Picker',
            'description' => 'A Date picker to work with friends analytical reports'
        ];  
    }   

    /**
     * {@inheritDoc}
     */
    public function render()
    {   
        $this->addJs('/modules/backend/formwidgets/datepicker/assets/js/datepicker.js');
        $this->addJs('js/date-picker.js');
        $this->addCss('css/datepicker.css');
        return $this->makePartial('widget');
    }   
}

