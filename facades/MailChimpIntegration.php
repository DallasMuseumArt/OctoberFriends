<?php namespace DMA\Friends\Facades;

use Illuminate\Support\Facades\Facade;

class MailChimpIntegration extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * Resolves to:
     * - DMA\Friends\Classes\Mailchimp\MailchimpManager
     *
     * @return string
     */
    protected static function getFacadeAccessor(){ 
        return 'mailchimpintegration';
    }
}
