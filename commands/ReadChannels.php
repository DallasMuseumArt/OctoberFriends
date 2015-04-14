<?php
namespace DMA\Friends\Commands;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Console\Command;
use Postman;

/**
 * Set a cron task that will retrive data for any active listenable channel
 *
 * @package DMA\Friends\Commands
 * @author Kristen Arnold, Carlos Arroyo
 */
class ReadChannels extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'friends:read-channels';

    /**
     * Read and process incomming data from listenable channels
     * @return void
     */
    public function fire()
    {
        Postman::readChannels();
    }
}