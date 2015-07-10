<?php namespace DMA\Friends\Classes\API;

use Log;
use App;
use Route;
use Response;
use Exception;
use System\Classes\PluginManager;
use DMA\Friends\Classes\API\BaseResource;
use DMA\Friends\Classes\API\NotFoundResource;


class APIManager
{
    /**
     * Internal use. Keep record of all register resources of the API
     * @var array
     */
    private $resources = [];
    
        
    /**
     * Regiter multiple API Resources using the following array 
     * structure:
     * 
     * [ <endpoint_url> => <classname_resource>,  <endpoint_url> => <classname_resource>, ...]
     * 
     * eg.
     *
     * [ 'activity' => 'DMA\Friends\API\resources\ActivityResource' ]
     */
    public function registerResources(array $resources)
    {
        $this->resources = array_merge($this->resources, $resources);
    }


    /**
     * Register a single API resource
     * @param string $url
     * @param string $resourceClassName
     */
    public function registerResource($url, $resourceClassName)
    {
        $this->resources[$url] = $resourceClassName;
    }

    /**
     * Get all register resources
     * @return array
     */
    public function getResources()
    {
        $this->loadRegisteredResources();
        return $this->resources;
    }

    /**
     * Register Laravel routes of each registered resources
     * @param bool $includeNamespaces
     */
    public function getRoutes($includeNamespaces=false)
    {
        // API Documentation
        // Document API using Swagger-PHP notation. The following router is for swagger-ui
        $this->addRouteApiDocs();

        foreach($this->getResources() as $url => $class){
            try{
                $resource = App::make($class);
                
                // Register additional routes first
                if(method_exists($resource, 'getAdditionalRoutes')){
                    $extra = $resource->getAdditionalRoutes();
                    foreach ($extra as $u => $args) {
                        $verbs = $args['verbs'];
                        foreach($verbs as $v) {
                            //Route::{$v}($url . '/' . $u, $class . "@" . $args['handler']);
                            
                            
                            // TODO : Find how to get current router group prefix
                            $baseName  = 'friends.api.' . str_replace('/', '.', strtolower($url));
                            if (!$routeName = array_get($args, 'name')){
                                $routeName = strtolower($args['handler']);
                            }
                            
                            $routeName = $baseName . '.' . $routeName;
                            
                            Route::{$v}($url . '/' . $u, [
                                    'as'   => $routeName,
                                    'uses' => $class . "@" . $args['handler']
                            ]);
                            
                            
                        }
                    }
                }
                
                if(is_subclass_of($resource, 'DMA\Friends\Classes\API\BaseResource')){
                    // Register resource
                    Route::resource($url, $class);
                } else if (is_subclass_of($resource, '\Controller')) {
                    // Register controller
                    Route::controller($url, $class);
                }

            }catch(Exception $e){
               Log::error("API : Resource endpoint fail to register due to '" . $e->getMessage() . "'");
            }
        }
        
        // Catch all
        Route::any('{all?}', function($path) { 
            return Response::api()->errorNotFound(); 
        })->where('all', '.+');
        
    }
    
    protected function addRouteApiDocs(){

        // API Docs
        Route::get('docs', [
            'as'   => 'friends.api.docs.index',
            'uses' => 'DMA\Friends\Classes\API\APIDocsController@index'
        ]);

    }
    
      
    
    /**
     * Loads registered FriendAPI resources from modules and plugins
     * @return void
     */
    public function loadRegisteredResources()
    {
        $plugins = PluginManager::instance()->getPlugins();
        foreach ($plugins as $pluginId => $pluginObj) {
            $resources = null;
            if(method_exists($pluginObj, 'registerFriendAPIResources')) {
                $resources = $pluginObj->registerFriendAPIResources();
            }
            if (!is_array($resources)) {
                continue;
            }
    
            $this->registerResources($resources);
        }
    }
}
