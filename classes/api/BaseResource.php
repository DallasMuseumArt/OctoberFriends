<?php namespace DMA\Friends\Classes\API;


use Model;
use Response;
use Illuminate\Routing\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use Illuminate\Pagination\Paginator;
use League\Fractal\TransformerAbstract;
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
            return Response::api()->withPaginator(new IlluminatePaginatorAdapter($paginator), $this->getTransformer());
        }else{
            return Response::api()->withCollection($model->all(), $this->getTransformer());
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
