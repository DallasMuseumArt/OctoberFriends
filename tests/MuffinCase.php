<?php

namespace DMA\Friends\Tests;

require_once(__DIR__ . '/../vendor/autoload.php');

use League\FactoryMuffin\Facade as FactoryMuffin;
use DMA\Friends\Plugin as DMAPlugin;
use App;

class MuffinCase extends \TestCase
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
