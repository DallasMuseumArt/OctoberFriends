<?php namespace DMA\Friends\Classes\API\Auth\Exceptions;

use Exception;

class UserAccessDenied extends Exception
{
    /**
     * @var integer $statusCode
     */
    protected $code = 403;
    protected $message = 'Access denied';
    
}