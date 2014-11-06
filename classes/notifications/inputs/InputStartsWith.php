<?php namespace DMA\Friends\Classes\Notifications\Inputs;

use DMA\Friends\Classes\Notifications\Inputs\InputContains;

/**
 * Simple Input that validates if a string contains the given string
 * @author carroyo
 *
 */
class InputStartsWith extends InputContains
{

    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\Notifications\Inputs\InputParser::getCode()
     */
    public static function getCode()
    {
    	return 'startswith';
    }

    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\Notifications\Inputs\InputRegex::getPattern()
     */
    public function getPattern()
    {
        $this->pattern =  sprintf('/^%s/%sm', $this->search, $this->options);
        return $this->pattern;
    }

}
