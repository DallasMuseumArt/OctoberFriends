<?php namespace DMA\Friends\Classes\Notifications\Twig;

use Twig_Node;
use Twig_Compiler;
use Twig_NodeInterface;
use Twig_Node_Expression;

/**
 * Represents a flash node
 *
 * @package DMA\Friends
 * @author Kristen Arnold, Carlos Arroyo
 */
class FlashMultipleNode extends Twig_Node
{
    public function __construct($name, Twig_NodeInterface $body, $lineno, $tag = 'flash')
    {
        parent::__construct(['body' => $body], ['name' => $name], $lineno, $tag);
    }

    /**
     * Compiles the node to PHP.
     *
     * @param Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(Twig_Compiler $compiler)
    {
        $attrib = $this->getAttribute('name');

        $compiler
            ->write('$_type = isset($context["type"]) ? $context["type"] : null;')
            ->write('$_message = isset($context["message"]) ? $context["message"] : null;')
        ;

        if ($attrib == 'all') {
            $compiler
                ->addDebugInfo($this)
                ->write('foreach (Flash::getMessages() as $type => $messages) {'.PHP_EOL)
                ->indent()
                    ->write('foreach ($messages as $message) {'.PHP_EOL)
                    ->indent()
                        ->write('$context["type"] = $type;')
                        ->write('$context["message"] = $message;')
                        ->subcompile($this->getNode('body'))
                    ->outdent()
                    ->write('}'.PHP_EOL)
                ->outdent()
                ->write('}'.PHP_EOL)
                ->write('Flash::purge();')
            ;
        }
        else {
            
            $compiler
                ->addDebugInfo($this)
                ->write('$context["type"] = ')
                ->string($attrib)
                ->write(';')
                ->write('foreach (Flash::get("' . $attrib . '") as $message) {'.PHP_EOL)
                ->indent()
                    ->write('$context["message"] = $message;')
                    ->subcompile($this->getNode('body'))
                ->outdent()
                ->write('}'.PHP_EOL)
                ->write('Flash::purge();')
            ;
            
        }
        
        $compiler
            ->write('$context["type"] = $_type;')
            ->write('$context["message"] = $_message;')
        ;
        
    }
}
