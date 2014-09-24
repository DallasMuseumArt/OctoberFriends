<?php

use League\FactoryMuffin\Facade as FactoryMuffin;
use DMA\Friends\Tests\MuffinCase;

class ActivityModelTest extends MuffinCase
{
    public function testCreateModelInstance()
    {
        $activity = FactoryMuffin::create('DMA\Friends\Models\Activity');
        $this->assertInstanceOf('DMA\Friends\Models\Activity', $activity);
    }

    public function testCanHaveTriggerTypes()
    {
        $activity = FactoryMuffin::create('DMA\Friends\Models\Activity');

        $triggerType = FactoryMuffin::create('DMA\Friends\Models\ActivityTriggerType');
        $this->assertInstanceOf('DMA\Friends\Models\ActivityTriggerType', $triggerType);

        $activity->triggerTypes()->save($triggerType);
    }
}
