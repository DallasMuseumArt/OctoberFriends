<?php namespace DMA\Friends\Tests;

use League\FactoryMuffin\Facade as FactoryMuffin;
use DMA\Friends\Plugin as DMAPlugin;
use App;
use TestCase;

class MuffinCase extends TestCase
{
    public static function setupBeforeClass()
    {   
        FactoryMuffin::loadFactories(__DIR__ . '/factories');
        $plugin = new DMAPlugin(new App);
        $plugin->boot();
    }  

    public static function tearDownAfterClass()
    {   
        FactoryMuffin::deleteSaved();
    } 

}
