<?php

use League\FactoryMuffin\Facade as FactoryMuffin;
use DMA\Friends\Tests\MuffinCase;
use DMA\Friends\Classes\FriendsLog;

class FriendsLogTest extends MuffinCase
{

    public function testWrite()
    {
        //($action, array $params)
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testActivityFails()
    {
        FriendsLog::activity([]);
    }

    public function testActivity()
    {
        $user = FactoryMuffin::create('DMA\Friends\Models\User');
        $activity = FactoryMuffin::create('DMA\Friends\Models\Activity');

        $params = [
            'object'    => $activity,
            'user'      => $user,
        ];

        FriendsLog::activity($params);
    }

    /**
     * @expectedException InvalidArgumentException 
     */
    public function testArtworkFails()
    {
        FriendsLog::artwork([]);
    }

    public function testArtwork()
    {
        $user = FactoryMuffin::create('DMA\Friends\Models\User');
        $params = [
            'user'          => $user,
            'artwork_id'    => '1998.12AD',
        ];

        FriendsLog::artwork($params);
    }

    /** 
     * @expectedException InvalidArgumentException 
     */
    public function testCheckinFails()
    {   
        FriendsLog::checkin([]);
    } 

    public function testCheckin()
    {
        $user = FactoryMuffin::create('DMA\Friends\Models\User');
        $location = FactoryMuffin::create('DMA\Friends\Models\Location');

        $params = [
            'object'    => $location,
            'user'      => $user,
        ];

        FriendsLog::checkin($params);
    }

    /** 
     * @expectedException InvalidArgumentException 
     */
    public function testPointsFails()
    {   
        FriendsLog::points([]);
    } 

    public function testPoints()
    {
        $user = FactoryMuffin::create('DMA\Friends\Models\User');

        $params = [
            'user'          => $user,
            'points_earned' => rand(),
        ];

        FriendsLog::points($params);
    }

    /** 
     * @expectedException InvalidArgumentException 
     */
    public function testRewardFails()
    {   
        FriendsLog::reward([]);
    } 

    public function testReward()
    {
        $user = FactoryMuffin::create('DMA\Friends\Models\User');
        $reward = FactoryMuffin::create('DMA\Friends\Models\Reward');

        $params = [
            'object'    => $reward,
            'user'      => $user,
        ];

        FriendsLog::reward($params);
    }

    /** 
     * @expectedException InvalidArgumentException 
     */
    public function testUnlockedFails()
    {   
        FriendsLog::unlocked([]);
    } 

    public function testUnlocked()
    {
        $user = FactoryMuffin::create('DMA\Friends\Models\User');
        $step = FactoryMuffin::create('DMA\Friends\Models\Step');

        $params = [
            'object'    => $step,
            'user'      => $user,
        ];

        FriendsLog::unlocked($params);
    }
}
