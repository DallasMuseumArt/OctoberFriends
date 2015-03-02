<?php namespace DMA\Friends\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Theme;
use SystemException;
use File;
use Lang;

class Modal extends ComponentBase
{

    use \System\Traits\ViewMaker;

    public function componentDetails()
    {
        return [
            'name'        => 'Modal Component',
            'description' => 'Render a partial in a modal dialog'
        ];
    }

    public function defineProperties()
    {
        return [
            'linkTitle' => [
                'title'     => 'Link Title',
                'default'   => 'Click here',
            ],
            'linkClasses' => [
                'title' => 'Additional link classes',
            ],
            'title' => [
                'title' => 'Modal Title',
            ],
            'partial' => [
                'title'         => 'Partial',
                'description'   => 'Name of the partial in your theme directory',
            ],
        ];
    }

    public function onRender()
    {
        $this->page['linkTitle'] = $this->property('linkTitle');
        $this->page['classes'] = $this->property('linkClasses');
    }

    public function onRun()
    {
        $this->addJs('/modules/system/assets/vendor/bootstrap/js/modal.js');
        $this->addJs('/modules/backend/assets/js/october.popup.js');
    }

    public function onRenderModal()
    {
        return $this->renderPartial('@modalDisplay', [
            'title'     => $this->property('title'),
            'content'   => $this->makePartial($this->property('partial')),
        ]);

    }

    /**
     * Implementation of ViewMaker->makePartial that renders a partial in
     * a modal dialog and can accept a partial from the active theme
     *
     * @param string $partial
     * Name of the partial to be rendered.  The partial must reside in the active
     * theme's "partials" directory
     *
     * @param array $params
     * An array of parameters to be passed to makePartial.  See Backend\Traits\ViewMaker 
     * for details
     *
     * @param boolean $throwException
     * if true an exception will be thrown if the partial is not available
     *
     * @return string $content
     * A rendered partial
     */
   
    public function makePartial($partial, $params = [], $throwException = true)
    {   
        $partialsDir = self::getThemePartialsDir();
        $partialPath = $partialsDir . $partial . '.htm';

        if (!File::isFile($partialPath)) {
            $partialPath = $this->getViewPath($partial) . '.htm';
        }

        if (!File::isFile($partialPath)) {
            if ($throwException)
                throw new SystemException(Lang::get('backend::lang.partial.not_found', ['name' => $partialPath]));
            else
                return false;
        }   

        return $this->makeFileContents($partialPath, $params);
    }   

    /**
     * Get the path to the theme's partials
     *
     * @return string $path
     */ 
    protected static function getThemePartialsDir()
    {
        $theme = Theme::getActiveTheme();
        return $theme->getPath() . '/partials/';
    }

}
