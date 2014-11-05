<?php namespace DMA\Friends\Classes\Notifications\Channels;

/**
 * Each channel which is listenable must extend from this interface.
 * @author Carlos Arroyo
 *
 */

interface Listenable
{

    /**
     * Implemented specific logic for each channel for
     * retriving incoming data through the channel.
     *
     * @return array
     * Array of DMA\Friends\Classes\Notifications\IncomingMessage
     */
    public function read();
}
