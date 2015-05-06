<?php namespace DMA\Friends\ReportWidgets;

use Backend\Classes\ReportWidgetBase;

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
            'id'            => $this->defaultAlias,
            'ajaxPath'      => $this->getAjaxPath(),
        ]);
        // $data = $this->onGenerateData();
        // return $this->makePartial('widget', ['data' => $data]);
    }

    protected function getAjaxPath()
    {
        return base_path() . $this->ajaxPath . get_class($this) . '@generateData';
    }

}