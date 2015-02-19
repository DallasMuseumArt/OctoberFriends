<?php namespace DMA\Friends\ReportWidgets;

use Backend\Classes\ReportWidgetBase;

/**
 * Helper class to instantiate assets for all report widgets that use c3
 */
class GraphReport extends ReportWidgetBase {

    /**
     * Add C3 javascript assets
     */
    public function addAssets()
    {
        $this->addJs('../../graphreport/assets/d3.js');
        $this->addJs('../../graphreport/assets/c3.js');
        $this->addCss('../../graphreport/assets/c3.css');    
    }
}