<?php
namespace DMA\Friends\Commands;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Indatus\Dispatcher\Scheduling\Schedulable;
use Indatus\Dispatcher\Scheduling\ScheduledCommand;
use Postman;

/**
 * Set a cron task that will retrive data for any active listenable channel
 *
 * @package DMA\Friends\Commands
 * @author Kristen Arnold, Carlos Arroyo
 */
class ReadChannels extends ScheduledCommand
{
    /**
     * @var string The console command name.
     */
    protected $name = 'friends:read-channels';

    /**
     * When a command should run
     * @param Scheduler $scheduler
     * @return \Indatus\Dispatcher\Scheduling\Schedulable
     */
    public function schedule(Schedulable $scheduler)
    {
        // Run every 5 minutes
        return $scheduler->everyMinutes(5);
    }

    /**
     * Read and process incomming data from listenable channels
     * @return void
     */
    public function fire()
    {
        Postman::readChannels();
    }
}