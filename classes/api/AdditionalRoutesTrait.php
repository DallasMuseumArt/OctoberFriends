<?php namespace DMA\Friends\Classes\API;

use Response;
use Exception;
use Log;

trait AdditionalRoutesTrait
{

    /**
     * Extra routes.
     * 
     * @var array
     */
    private $additionalRoutes = [];

    /**
     * Add extra routes to a resource endpoints.
     * 
     * @param string $handler
     * Name of the method within the resource that will handler this route
     * 
     * @param string $url
     * String URL. This can be expressed as Laravel URL route eg. checkin/{code}
     * 
     * @param array $verbs
     * HTTP verb methods. Default is ['GET']
     * 
     * @param string $name
     * Name of the url. If not name is given the string representation of the handler will be used.
     */
    public function addAdditionalRoute($handler, $url=Null, array $verbs=['GET'], $name=Null)
    {
        if( method_exists( $this, $handler ) ) {
            // 2015-12-07 : Fixed wrong assignation of URL suffix
            // whe $url is empty
            $url = ($url) ? $url : '';//$handler;
            $this->additionalRoutes[$url] = [
                'handler' => $handler,
                'verbs' => $verbs,
                'name'  => $name    
            ];
        }
    }

    /**
     * Get all additional routes
     * @return array
     */
    public function getAdditionalRoutes()
    {
        return $this->additionalRoutes;
    }
    
    
    /**
     * Catch all missing HTTP verbs
     * @see \Illuminate\Routing\Controller::missingMethod()
     */
    public function missingMethod($parameters = array())
    {
        return Response::api()->errorForbidden();
    }


}
