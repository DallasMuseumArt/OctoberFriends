<?php namespace DMA\Friends\Classes\Notifications\Inputs;

use DMA\Friends\Classes\Notifications\Inputs\InputRegex;

/**
 * Simple Input that validates if a string contains the given string
 * @author carroyo
 *
 */
class InputContains extends InputRegex
{

    protected $search;
    protected $options;
    protected $normalizeWhiteSpace;

    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\Notifications\Inputs\InputParser::getCode()
     */
    public static function getCode()
    {
        return 'contains';
    }

    /**
     *
     * @param string $search
     * Test to be search
     *
     * @param boolean $caseInsensitive
     * Default is false.
     *
     * @param boolean $normalizeWhiteSpace
     * Remove extra whitespace.
     */
    public function __construct($search, $caseInsensitive=true, $normalizeWhiteSpace=true)
    {
       if($search == ''){
           throw new \Exception('$search can not be empty');
       }

       if ($caseInsensitive){
           $this->options = $this->options . 'i';
       }

       $this->normalizeWhiteSpace = $normalizeWhiteSpace;
       $this->search = $this->clean($search);
    }

    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\Notifications\Inputs\InputRegex::clean()
     */
    public function clean($string)
    {
        if($this->normalizeWhiteSpace){
            $string = $this->normalizeWhiteSpace($string);
        }
        return parent::clean($string);
    }

    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\Notifications\Inputs\InputRegex::getPattern()
     */
    public function getPattern()
    {
        $this->pattern = sprintf('/%s/%sm', $this->search, $this->options);
        return $this->pattern;
    }

}
