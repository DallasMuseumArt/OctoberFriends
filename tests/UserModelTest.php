<?php

use League\FactoryMuffin\Facade as FactoryMuffin;
use RainLab\User\Models\User;
use DMA\Friends\Tests\MuffinCase;

class UserModelTest extends MuffinCase
{
    public function __construct()
    {
    }

    public function testCreateModelInstance()
    {
        $user = FactoryMuffin::create('RainLab\User\Models\User');
        $this->assertInstanceOf('RainLab\User\Models\User', $user);
    }

    public function testCanHaveBadges()
    {
        $user = FactoryMuffin::create('RainLab\User\Models\User');

        $badge = FactoryMuffin::create('DMA\Friends\Models\Badge');
        $this->assertInstanceOf('DMA\Friends\Models\Badge', $badge);
    
        $user->badges()->save($badge);

        $this->assertEquals($user->badges[0]->id, $badge->id);
    }

    public function testCanHaveRewards()
    {
        $user = FactoryMuffin::create('RainLab\User\Models\User');

        $reward = FactoryMuffin::create('DMA\Friends\Models\Reward');
        $this->assertInstanceOf('DMA\Friends\Models\Reward', $reward);

        $user->rewards()->save($reward);

        $this->assertEquals($user->rewards[0]->id, $reward->id);
    }

    /**
     * TODO: this will eventually need to be rewritten to accomodate 
     * updated step logic
     */
    public function testCanHaveSteps()
    {
        $user = FactoryMuffin::create('RainLab\User\Models\User');

        $step = FactoryMuffin::create('DMA\Friends\Models\Step');
        $this->assertInstanceOf('DMA\Friends\Models\Step', $step);

        $user->steps()->save($step);

        $this->assertEquals($user->steps[0]->id, $step->id);
    }

}
