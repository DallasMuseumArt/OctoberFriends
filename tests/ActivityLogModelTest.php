<?php

use League\FactoryMuffin\Facade as FactoryMuffin;
use DMA\Friends\Tests\MuffinCase;
use DMA\Friends\Models\ActivityLog;

class ActivityLogModelTest extends MuffinCase
{
    public function testCreateModelInstance()
    {
        $activityLog = FactoryMuffin::create('DMA\Friends\Models\ActivityLog');
        $this->assertInstanceOf('DMA\Friends\Models\ActivityLog', $activityLog);
    }

    public function testRequirements()
    {
        $activityLog = FactoryMuffin::create('DMA\Friends\Models\ActivityLog');
        $activityLog->user_id = null;

        try {
            $activityLog->save();
        } catch (Exception $e) {}

        $errors = $activityLog->errors()->all();
                
        $this->assertCount(1, $errors);
        $this->assertEquals($errors[0], 'The user id field is required.');

    }

    public function testActionTypes()
    {
        // Test that valid actions do not fail
        $activity = new ActivityLog;

        $actionTypes = $activity->actionTypes;

        foreach ($actionTypes as $action) {
            $activityLog = FactoryMuffin::create('DMA\Friends\Models\ActivityLog'); 
            $activityLog->action = $action;

            $this->assertTrue($activityLog->save());
        }

        // Test that invalid action fails
        $activityLog = FactoryMuffin::create('DMA\Friends\Models\ActivityLog');
        $activityLog->action = 'maryhadalittlelamb';

        try {
            $activityLog->save();
        } catch (Exception $e) {}

        $errors = $activityLog->errors()->all();
            
        $this->assertCount(1, $errors);
        $this->assertEquals($errors[0], 'The selected action is invalid.');
    }

    public function testRelationships()
    {
        
        $object_types = [
            FactoryMuffin::create('DMA\Friends\Models\Activity'),
            FactoryMuffin::create('DMA\Friends\Models\Badge'),
            FactoryMuffin::create('DMA\Friends\Models\Reward'),
            FactoryMuffin::create('DMA\Friends\Models\Step'),
        ];

        foreach($object_types as $object) {
            $activityLog = FactoryMuffin::create('DMA\Friends\Models\ActivityLog');
            $object->activityLogs()->save($activityLog);
        }

    }

}
