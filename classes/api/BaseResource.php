<?php namespace DMA\Friends\Classes\API;


use Model;
use Input;
use Response;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\Paginator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\TransformerAbstract;
use DMA\Friends\Classes\API\FilterSpec;
use DMA\Friends\Classes\API\AdditionalRoutesTrait;


class BaseResource extends Controller {

    use AdditionalRoutesTrait;

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

    
    
    protected function getPageSize()
    {
        return Input::get('per_page', $this->pageSize);
    }

    protected function getSortBy()
    {
        $sortBy = Input::get('sort', []);
        $sortBy = explode(',', $sortBy);
        $sortBy = array_map(function($s) {
            $s = strtolower(trim($s));
            return $s;
        }, $sortBy);
        return $sortBy;
    }
    
        
    protected function getFilters()
    {
        $filters=[];
        $ignoreParameter = ['per_page','page', 'sort'];
        
        foreach(Input::all() as $key => $value) {
            if (!in_array($key, $ignoreParameter)) {
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
     * Display a listing of items
     *
     * @return Response
     */
    public function index()
    {
        try {
            $filters    = $this->getFilters();
            $sortBy     = $this->getSortBy();
            $model      = $this->getModel();
            $query      = $model->applyFiltersToQuery($filters);
            $query      = $model->applySortByToQuery($sortBy);
            $pageSize   = $this->getPageSize(); 
            
            if ($pageSize > 0){
                $paginator = $query->paginate($pageSize);
                return Response::api()->withPaginator(new IlluminatePaginatorAdapter($paginator), $this->getTransformer());
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
            return Response::api()->errorInternalError();
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

}
