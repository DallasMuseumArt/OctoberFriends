<?php namespace DMA\Friends\Tests;

use League\FactoryMuffin\Facade as FactoryMuffin;
use DMA\Friends\Tests\MuffinCase;

class RewardModelTest extends MuffinCase
{
    public function __construct()
    {
    }

    public function testCreateModelInstance()
    {
        $reward = FactoryMuffin::create('DMA\Friends\Models\Reward');
        $this->assertInstanceOf('DMA\Friends\Models\Reward', $reward);
    }
}
