<?php namespace DMA\Friends\API\Resources;

use DMA\Friends\Classes\API\BaseResource;
use Swagger\Annotations as SWG;


class StepResource extends BaseResource {

    //protected $pageSize     = 0;
    protected $model        = '\DMA\Friends\Models\Step';

    protected $transformer  = '\DMA\Friends\API\Transformers\StepTransformer';

    /**
     * @SWG\Get(
     *     path="steps",
     *     description="Returns all activity steps",
     *     summary="Return all steps",
     *     tags={ "steps"},
     *         
     *     @SWG\Parameter(
     *         ref="#/parameters/per_page"
     *     ),
     *     @SWG\Parameter(
     *         ref="#/parameters/page"
     *     ),
     *     @SWG\Parameter(
     *         ref="#/parameters/sort"
     *     ),       
     *       
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/step", type="array")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Not Found",
     *         @SWG\Schema(ref="#/definitions/error404")
     *    )
     * )
     */
    public function index()
    {
        return parent::index();
    }
    
    /**
     * @SWG\Get(
     *     path="steps/{id}",
     *     description="Returns a step by id",
     *     summary="Find a step by id",
     *     tags={ "steps"},
     *      
     *     @SWG\Parameter(
     *         description="ID of step to fetch",
     *         format="int64",
     *         in="path",
     *         name="id",
     *         required=true,
     *         type="integer"
     *     ),
     *     
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/step")
     *     ),
     *     @SWG\Response(
     *         response=500,
     *         description="Unexpected error",
     *         @SWG\Schema(ref="#/definitions/error500")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Not Found",
     *         @SWG\Schema(ref="#/definitions/error404")
     *     )
     * )
     */
    public function show($id)
    {
        return parent::show($id);
    }
    
    
    
}
