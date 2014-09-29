<?php namespace DMA\Friends\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Theme;
use System\Classes\SystemException;
use File;
use Lang;

class Modal extends ComponentBase
{

    use \Backend\Traits\ViewMaker;

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
            'title' => [
                'title' => 'Modal Title',
            ],
            'linkTitle' => [
                'title'     => 'Link Title',
                'default'   => 'Click here',
            ],
            'partial' => [
                'title'     => 'Partial',
                'default'   => false,
            ],
        ];
    }

    public function onRender()
    {
        $this->page['linkTitle'] = $this->property('linkTitle');
    }

    public function onRun()
    {
        $this->addCss('/modules/backend/assets/css/october.css');
        $this->addJs('/modules/system/assets/vendor/bootstrap/js/modal.js');
        $this->addJs('/modules/backend/assets/js/october.popup.js');
    }

    public function onRenderModal()
    {
        return $this->makePartial('modal', [
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
