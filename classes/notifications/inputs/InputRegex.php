<?php namespace DMA\Friends\Classes\Notifications\Inputs;

use DMA\Friends\Classes\Notifications\Inputs\InputParser;
use string;

class InputRegex implements InputParser
{

    protected $pattern;

    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\Notifications\Inputs\InputParser::getCode()
     */
    public static function getCode()
    {
    	return 'regex';
    }

    /**
     * @param string $pattern
     */
    public function __construct($pattern)
    {
        $this->pattern = $pattern;
    }


    /**
     * Regex expresion use for the parser to
     * verify the input message
     * @return string
     */
    public function getPattern(){
        return $this->pattern;
    }

    /**
     * Normailize whitespace in a string
     * useful when comparing strings
     * @param string $input
     * @return $string
     */
    public function normalizeWhiteSpace($input)
    {
    	// Remove leading and ending white spaces
    	$input = trim($input);

    	// Normalize white spaces
    	$input = preg_replace('/[ ]{2,}/i', ' ', $input);
    	return $input;
    }


    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\Notifications\Inputs\InputParser::clean()
     */
    public function clean($string)
    {
    	return $string;
    }

    /**
     * Test if the input text match the regular expression
     * in this parser.
     * @param string $input
     * @return bool
     */
    public function valid($input)
    {
        try{
            return count($this->applyRegex($input)) > 0;
        }catch(\Exception $e){
            \Log::error('Error validating input:' . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritDoc}
     * @see \DMA\Friends\Classes\Notifications\InputParser::getMatchText()
     */
    public function getMatches($input)
    {
       return $this->applyRegex($input);
    }

    /**
     * Apply a regex expression to an input text and
     * returns and array of all matches.
     * @param string $input
     * @throws \Exception
     * @return array
     */
    protected function applyRegex($input)
    {
        $pattern = $this->getPattern();
        if(!is_null($pattern)){
            $input = $this->clean($input);
            preg_match($pattern, $input, $matches);
            return $matches;
        }else{
            throw new \Exception('Regex pattern is not defined for this parser');
        }
        return array();
    }
}
