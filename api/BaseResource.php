<?php namespace DMA\Friends\Api;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\TransformerAbstract;
use Response;
use Model;

class BaseResource extends \Controller {

    /**    
     * @var string Eloquent model name 
     */
    protected $model;
    
    /**
     * @var League\Fractal\TransformerAbstract classname of the transformer should be use
     */
    protected $transformer = 'DMA\Friends\Api\BaseTransformer';
    
    /**
     * @var int 
     */
    protected $pageSize = 50;
        
    
    /**
     * Create and return an instance of a GenericModelRepository
     * @var GenericModelRepository   
     */
    private function getModel()
    {
        if (!is_null($this->model))
        {
            $this->model = new GenericModelRepository($this->model);
        }
        return $this->model;
    }
    
    /**
     * Display a listing of items
     *
     * @return Response
     */
    public function index()
    {
        $model = $this->getModel();
        if ($this->pageSize > 0){
            $paginator = $model->paginate($this->pageSize);
            return Response::api()->withPaginator(new IlluminatePaginatorAdapter($paginator), new $this->transformer);
        }else{
            return Response::api()->withCollection($model->all(), new $this->transformer);
        }
    }
    
    /**
     * Show the form for creating a new item
     *
     * @return Response
     */
    public function create()
    {
        return $this->errorForbidden();
    }
    
    /**
     * Store a newly created item in storage.
     *
     * @return Response
     */
    public function store()
    {
        return $this->errorForbidden();
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
            return Response::api()->withItem($instance, new $this->transformer);
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

class BaseTransformer extends TransformerAbstract{
    /**
     * Return all attributes of the model
     * @var $instance October\Model 
     * @param array 
     */
    public function transform(Model $instance){
        return $instance->getAttributes();
    }    
}

class GenericModelRepository {

    protected $modelClassName;
    
    public function __construct($modelClassName)
    {
        $this->modelClassName = $modelClassName;
    }
    
    private function call($method, $parameters=array())
    {
        return call_user_func_array("{$this->modelClassName}::{$method}", $parameters);
    }

    public function create(array $attributes)
    {
        return $this->call("create", array($attributes));
    }

    public function all($columns = array('*'))
    {
        return  $this->call("all", array($columns));
    }

    public function find($id, $columns = array('*'))
    {
        return  $this->call("find", array($id, $columns));
    }

    public function findOrFail($id, $columns = array('*'))
    {
        return  $this->call("findOrFail", array($id, $columns));
    }    

    public function destroy($ids)
    {
        return  $this->call("destroy", array($ids));
    }
    
    public function paginate($perPage = null, $columns = array('*'))
    {
         return $this->call("paginate", array($perPage, $columns));
    }

}

