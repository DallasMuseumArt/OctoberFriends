<?php namespace DMA\Friends\Classes\API\Auth\Exceptions;

use Exception;

class TokenExpiredException extends Exception
{
    /**
     * @var integer $statusCode
     */
    protected $code = 401;
    
    
    
}