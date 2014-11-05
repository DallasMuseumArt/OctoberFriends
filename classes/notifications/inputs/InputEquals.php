<?php namespace DMA\Friends\Classes\Notifications\Inputs;

use DMA\Friends\Classes\Notifications\Inputs\InputContains;

/**
 * Simple Input that validates if a string contains the given string
 * @author carroyo
 *
 */
class InputEquals extends InputContains
{

    /**
     * @param mixed $search
     * @param boolean $caseInsensitive
     * @param boolean $normalizeWhiteSpace
     */
    public function __construct($search, $caseInsensitive=true, $normalizeWhiteSpace=false)
    {
        parent::__construct($search, $caseInsensitive, $normalizeWhiteSpace);
    }


    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\Notifications\Inputs\InputParser::getCode()
     */
    public static function getCode()
    {
    	return 'equals';
    }

    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\Notifications\Inputs\InputRegex::getPattern()
     */
    public function getPattern()
    {
        $this->pattern = sprintf('/^%s$/%sm', $this->search, $this->options);
        return $this->pattern;
    }

}
