<?php namespace DMA\Friends\Classes\API;

use View;
use Response;
use Illuminate\Routing\Controller;


class APIDocsController extends Controller {
    
    public function index()
    {
        // Temporal solution
        $content = View::make('dma.friends::api-docs', [])->render();
        return Response::make($content, 200);
    }
        
}