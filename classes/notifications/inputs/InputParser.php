<?php namespace DMA\Friends\Classes\Notifications\Inputs;

interface InputParser
{

    /**
     * Returns unique indentifier for this parser.
     * @return string
     */
    public static function getCode();


    /**
     * Test if the input text match the regular expression
     * in this parser.
     * @param string $input
     * @return bool
     */
    public function valid($input);


    /**
     * Clean string
     * @param string $string
     * @return string
     */
    public function clean($string);


    /**
     * Return match text found in the $input string
     * @param string $input
     */
    public function getMatches($input);
}
