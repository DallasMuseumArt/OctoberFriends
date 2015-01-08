<?php namespace DMA\Friends\Classes\Notifications\Templates;

use Closure;
use DMA\Friends\Models\Settings;
use DMA\Friends\Classes\Notifications\Channels\Listenable;


/**
 * DMA Template notification parser.
 * @author Carlos Arroyo
 */
class TemplateParser
{
    // Inspired by October\Rain\Mail\MailParser 

    const SECTION_SEPARATOR = '==';
    
    /**
     * Parses Notifications template content.
     * The expected file format is following:
     * <pre>
     * Settings section
     * ==
     * alternative template
     * ==
     * main template
     * </pre>
     * @param string $content Specifies the file content.
     * @return array Returns an array with the following indexes: 'settings', 'main', 'alternative'.
     * The 'settings' element contains the parsed INI file as array.
     */
    public static function parse($content)
    {
        $sections = preg_split('/^={2,}\s*/m', $content, -1);
        $count = count($sections);
        foreach ($sections as &$section)
            $section = trim($section);
    
        $result = [
                'settings' => [],
                'main' => null,
                'alternative' => null
        ];
    
        if ($count >= 3) {
            $result['settings'] = parse_ini_string($sections[0], true);
            $result['alternative'] = $sections[1];
            $result['main'] = $sections[2];
        } elseif ($count == 2) {
            $result['settings'] = parse_ini_string($sections[0], true);
            $result['main'] = $sections[1];
        } elseif ($count == 1) {
            $result['main'] = $sections[0];
        }

        return $result;
    }
    

}
