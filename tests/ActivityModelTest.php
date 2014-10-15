<?php

use League\FactoryMuffin\Facade as FactoryMuffin;
use DMA\Friends\Tests\MuffinCase;
use DMA\Friends\Models\Activity;

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

    public function testTimeRestrictionsAreSerialized()
    {
        $activity = FactoryMuffin::create('DMA\Friends\Models\Activity');

        $timeRestrictionData = [
            'start_time'    => '11:00AM',
            'end_time'      => '12:00PM',
            'days'          => [
                1   => true,
                2   => false,
                3   => true,
                4   => false,
                5   => true,
                6   => false,
            ],
        ];

        $activity->time_restriction_data = $timeRestrictionData;

        $activity->save();

        // Load a new reference to the model
        $newActivity = Activity::find($activity->id);

        // Compare time_restriction_data to ensure that attributes are serialized/unserialized properly
        $this->assertEquals($newActivity->time_restriction_data, $timeRestrictionData);
    }
}
