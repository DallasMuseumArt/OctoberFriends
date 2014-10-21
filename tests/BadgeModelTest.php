<?php

use League\FactoryMuffin\Facade as FactoryMuffin;
use DMA\Friends\Tests\MuffinCase;

class BadgeModelTest extends MuffinCase
{
    public function __construct()
    {
    }

    public function testCreateModelInstance()
    {
        $badge = FactoryMuffin::create('DMA\Friends\Models\Badge');
        $this->assertInstanceOf('DMA\Friends\Models\Badge', $badge);
    }

    public function testCanHaveSteps()
    {
        $badge = FactoryMuffin::create('DMA\Friends\Models\Badge');

        $step = FactoryMuffin::create('DMA\Friends\Models\Step');
        $this->assertInstanceOf('DMA\Friends\Models\Step', $step);

        $badge->steps()->save($step);

        $this->assertEquals($badge->steps[0]->id, $step->id);

    }

    public function testCanHaveCategories()
    {   
        $badge = FactoryMuffin::create('DMA\Friends\Models\Badge');

        $category = FactoryMuffin::create('DMA\Friends\Models\Category');
        $this->assertInstanceOf('DMA\Friends\Models\Category', $category);

        $badge->categories()->save($category);
    }  
}
