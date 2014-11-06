<?php namespace DMA\Friends\Classes\Notifications\Channels;

/**
 * Each channel which is listenable and they API uses webhooks to get incoming data
 * like Twilio.
 * @author Carlos Arroyo
 *
 */

interface Webhook
{

    /**
     * Implemented specific logic for each channel for
     * retriving processing webhooks.
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function webhook(array $request);
}
