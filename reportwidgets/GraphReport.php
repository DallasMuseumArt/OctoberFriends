<?php namespace DMA\Friends\ReportWidgets;

use Backend\Classes\ReportWidgetBase;
use URL;

/**
 * Helper class to instantiate assets for all report widgets that use c3
 */
class GraphReport extends ReportWidgetBase {

    protected $partialPath = '/plugins/dma/friends/reportwidgets/graphreport/partials/';
    protected $ajaxPath = '/friends/reports/ajax/';

    /**
     * Add C3 javascript assets
     */
    public function addAssets()
    {
        $this->addJs('../../graphreport/assets/d3.js');
        $this->addJs('../../graphreport/assets/c3.js');
        $this->addJs('../../graphreport/assets/graphreport.js');
        $this->addJs('graph.js');
        $this->addCss('../../graphreport/assets/c3.css'); 
    }

    /**
     * Render a graph
     */
    public function render()
    {
        $this->addAssets();
        return $this->makePartial('@' . $this->partialPath . '_widget.htm', [
            'title'         => $this->widgetTitle,
            'id'            => $this->defaultAlias,
            'ajaxPath'      => $this->getAjaxPath(),
        ]);
    }

    protected function getAjaxPath()
    {
        $class = get_class($this);
        // forward slashes do not get preserved in javascript so lets replace them
        $class = str_replace('\\', '@', $class);
        return URL::to('/') . $this->ajaxPath . $class;
    }

}