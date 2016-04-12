<?php namespace DMA\Friends\Classes\API;


use Log;
use Model;
use Input;
use Request;
use Response;
use Exception;

//use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;

use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\TransformerAbstract;

use DMA\Friends\Classes\API\ModelRepository;
use DMA\Friends\Classes\API\FilterSpec;
use DMA\Friends\Classes\API\AdditionalRoutesTrait;
use DMA\Friends\Classes\API\Auth\UserAccessLevelTrait;

use October\Rain\Database\Builder;



class BaseResource extends Controller {

    use AdditionalRoutesTrait;
    use UserAccessLevelTrait;
    
    /**
     * @var string Eloquent model name
     */
    protected $model;


    /**
    * @var DMA\Friends\Clasess\API\ModelRepository
    */
    protected $modelRepository;

    /**
     * @var League\Fractal\TransformerAbstract classname of the transformer should be use
     */
    protected $transformer = 'DMA\Friends\Classes\API\BaseTransformer';

    /**
     * @var int
     */
    protected $pageSize = 50;
    
    /**
     * Laravel resources actions allowed for this resource
     * @var array
     */
    public $allowActions = ['index', 'show', 'store', 'update', 'destroy'];


    /**
     * List of actions that don't requiered API Authentication
     * @var array
     */
    public $publicActions = [];
    
    
    /**
     * Exclude filters 
     */
    public $excludeFilters = [];
    
    /**
     * Create and return an instance of a ModelRepository or the configure model

     * @return GenericModelRepository
     */
    protected function getModel()
    {
        if (is_null($this->modelRepository))
        {
            $this->modelRepository = new ModelRepository($this->model);
        }
        return $this->modelRepository;
    }

    /**
     * Create a new instance of the transformer of this resource
     * @return DMA\Friends\Classes\API\BaseTransformer
     */
    protected function getTransformer()
    {
        return new $this->transformer;
    }

    
    /**
     * Get size of the page
     * @return integer
     */
    protected function getPageSize()
    {
        return Input::get('per_page', $this->pageSize);
    }

    /**
     * Get fields to sort by
     * @return array
     */
    protected function getSortBy()
    {
        $sortBy = Input::get('sort', '');
        $sortBy = explode(',', $sortBy);
        $sortBy = array_map(function($s) {
            $s = strtolower(trim($s));
            return $s;
        }, $sortBy);
        return $sortBy;
    }
    
    
    /**
     * Return list of URL parameters that should be 
     * exclude of the list of filters to apply to the 
     * queryset.
     * 
     * @return array
     */
    protected function getExcludedFiltersList()
    {
        return $this->excludeFilters;
    }
    
    
    /**
     * Get filters to apply to this resource
     * @return array of \DMA\Friends\Classes\API\FilterSpec
     */ 
    protected function getFilters()
    {
        $filters=[];
        $ignoreParameters = array_unique(array_merge(['per_page','page', 'sort'], $this->getExcludedFiltersList()));
        
        // Adding the URL path to the $ignoreParameter helps to
        // manage an issue that only happens when using Nginx to server
        // OctooberCMS. The problem is that depending on how is configured
        // Nginx causes that sometimes to include the URL path of 
        // the response of Input:all()
        $ignoreParameters[] = Request::path();
        $ignoreParameters[] = '/' . Request::path();

        foreach(Input::all() as $key => $value) {
            if (!in_array($key, $ignoreParameters)) {
                // Separate operator and filter name
                $bits = explode('__', $key);
                
                $filter   = $bits[0];
                $operatorAlias = 'exact';
                
                if (count($bits) > 1) {
                    $operatorAlias = $bits[1];
                }
    
                // Create instance of a filter field specification
                $filterSpec = new FilterSpec($filter, $value, $operatorAlias);
                $filters[] = $filterSpec;
            }
        }
        return $filters;
    }
    
    /**
     * Helper function to apply filter and sort paremeters to the given
     * ModelRepository.
     *
     * @param ModelRepository $model
     * @throws \Illuminate\Database\QueryException
     * @return \October\Rain\Database\Builder
     */
    protected function applyFilters(ModelRepository $model = null)
    {
        try {
            $model      = (is_null($model)) ? $this->getModel() : $model;
            $filters    = $this->getFilters();
            $sortBy     = $this->getSortBy();
            $query      = $model->applyFiltersToQuery($filters);
            $query      = $model->applySortByToQuery($sortBy, $query);
    
        } catch(\Exception $e) {
    
            $message = $e->getMessage();
            \Log::error('API endpoint ' . get_class($this) . ' when applying filters: ' . $e->getMessage());
    
            // Re-throw exception
            throw $e;
        }
        return $query;
         
    }
    
    /**
     * Paginate resultset 
     * 
     * @param Builder $query
     * @return Response
     */
    protected function paginateResult(Builder $query)
    {
        try {
            $pageSize   = $this->getPageSize();
    
            if ($pageSize > 0){
                $paginator = $query->paginate($pageSize);
                return Response::api()->withPaginator($paginator, $this->getTransformer());
            }else{
                return Response::api()->withCollection($query->get(), $this->getTransformer());
            }
        } catch(\Exception $e) {
    
            $message = $e->getMessage();
            if( $e instanceof QueryException){
                \Log::error('API endpoint ' . get_class($this) . ' : ' . $e->getMessage());
                $message = 'One or multiple filter fields does not exists in the model';
            }
            return Response::api()->errorInternalError($message);
        }
    }
    


    /**
     * Display a listing of items
     *
     * @return Response
     */
    public function index()
    {
        try {
            $model      = $this->getModel();
            $query      = $this->applyFilters($model);
            
            // Paginate result
            return $this->paginateResult($query);
        } catch(\Exception $e) {
            // Send exception to log
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            
            $message = $e->getMessage();
            return Response::api()->errorInternalError($message);
        }
    }
    
    /**
     * Show the form for creating a new item
     *
     * @return Response
     */
    public function create()
    {
        return Response::api()->errorForbidden();
    }

    /**
     * Store a newly created item in storage.
     *
     * @return Response
     */
    public function store()
    {
        return Response::api()->errorForbidden();
    }

   
    /**
     * Display the specified item.
     *
     * @param  int  $id
     * @return Response
     *
     */    
    public function show($id)
    {
        try {
            $model = $this->getModel();
            $instance = $model->findOrFail($id);
            return Response::api()->withItem($instance, $this->getTransformer());
        }catch(ModelNotFoundException $e) {
            return Response::api()->errorNotFound();
        }catch(Exception $e){
            // Send exception to log
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
            
            return Response::api()->errorInternalError($e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified item.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        return Response::api()->errorForbidden();
    }

    /**
     * Update the specified item in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        return Response::api()->errorForbidden();

    }

    /**
     * Remove the specified item from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        return Response::api()->errorForbidden();

    }

    
    /**
     * Helper method to update models attributes
     * @param unknown $model
     * @param array $data
     * @param array $attrsToUpdate
     * @return unknown
     */
    protected function updateModelData($model, $data, $attrsToUpdate)
    {
        foreach ($attrsToUpdate as $attr) {
            $new_value = array_get($data, $attr, null);
            if(!is_null($new_value)) {
                $model->{$attr} = $new_value;
            }
        }
        return $model;
    }
    
    /**
     * Generates a response with a 422 HTTP header a given message and given errors.
     *
     * @param string $message
     * @param array $errors
     * @return mixed
     */
    protected function errorDataValidation($message = 'Invalid data ', $errors = [])
    {
        return Response::api()->setStatusCode(422)->withArray([
                'error' => [
                        'code' => 422,
                        'http_code' => 'GEN-UNPROCESSABLE',
                        'message' => $message,
                        'errors' => $errors
                ]
        ]);
    }
    
    /**
     * (non-PHPdoc)
     * @see \Illuminate\Routing\Controller::callAction()
     */
    public function callAction($method, $parameters)
    {
        try{
            // If the user don't had access to this resource
            // validateUserAccess will throw an exection
            $this->validatedUserAccess($method, $parameters);

            
            // call menthod an return response the user has 
            // permission access
            return parent::callAction($method, $parameters);
            
         }catch(Exception $e){
            //Send exception to log
            Log::error($e->getMessage());
            Log::error($e->getTraceAsString());
    
            $message = $e->getMessage();
            $responseMethod  = array_get([
                    400 => 'errorWrongArgs',
                    401 => 'errorUnauthorized',
                    403 => 'errorForbidden',
                    404 => 'errorNotFound',
                    405 => 'errorMethodNotAllowed',
                    410 =>  'errorGone'
                 
            ], $e->getCode(), 'errorInternalError');
            
            return Response::api()->{$responseMethod}($message);
        }
    }
    
    
}
