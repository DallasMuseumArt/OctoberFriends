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
     */
    public function addAdditionalRoute($handler, $url=Null, array $verbs=['GET'])
    {
        if( method_exists( $this, $handler ) ) {
            $url = ($url) ? $url : $handler;
            $this->additionalRoutes[$url] = [
                'handler' => $handler,
                'verbs' => $verbs
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
     * (non-PHPdoc)
     * @see \Illuminate\Routing\Controller::callAction()
     */
    public function callAction($method, $parameters)
    {
        try{
            return parent::callAction($method, $parameters);
        } catch(Exception $e) {
            // Send exception to log 
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            
            $message = $e->getMessage();
            return Response::api()->errorInternalError($message);
        }
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
