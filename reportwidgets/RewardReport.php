<?php namespace DMA\Friends\ReportWidgets;

use DB;

class RewardReport extends GraphReport
{
    public $defaultAlias = 'RewardReport';

    /**
     * {@inheritDoc}
     */
    public function widgetDetails()
    {
        return [
            'name'        => 'Activities By Day',
            'description' => 'Show some basic statistics on friends'
        ];
    }

    public function render()
    {
        $this->addAssets();
        $data = $this->onGenerateData();
        return $this->makePartial('widget', ['data' => $data]);
    }

    public function addAssets()
    {
        $this->addJs('activitiesbyday.js');
        parent::addAssets();
    }

    public function onGenerateData()
    {
    }
}