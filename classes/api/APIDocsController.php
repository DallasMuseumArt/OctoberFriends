<?php namespace DMA\Friends\Classes\API;

use Illuminate\Routing\Controller;


class APIDocsController extends Controller {
    
    public function swaggerDocs()
    {
        return 'now';
        
    }
    
    
    protected function addRouteApiDocs(){

        /*
        $app->after(function ($request, $response) use($app)
        {
            // Update asset URLs for October
            $content = $response->getContent();
            $content = $this->updateAssetUrls($content);
            $response->setContent($content);
        });
        */
    }
    
}