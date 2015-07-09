<?php namespace DMA\Friends\Classes\API;

use View;
use Response;
use Illuminate\Routing\Controller;


class APIDocsController extends Controller {
    
    
    public function __construct()
    {
        $this->afterFilter(function ($route, $req, $resp) {
            $resp->headers->set('Access-Control-Allow-Origin', $_SERVER['HTTP_HOST']);
            return $resp;
        });
    }
    
    
    public function index()
    {
        // Temporal solution
        $content = View::make('dma.friends::api-docs', [])->render();
        return Response::make($content, 200);
    }
        
}