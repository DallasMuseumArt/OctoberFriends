<?php namespace DMA\Friends\API\Resources;

use DMA\Friends\Classes\API\BaseResource;

class CategoryResource extends BaseResource {

    protected $model        = '\DMA\Friends\Models\Category';
    protected $transformer  = '\DMA\Friends\API\Transformers\CategoryTransformer';

    /**
     * @SWG\Get(
     *     path="categories",
     *     description="Returns all categories",
     *     summary="Return all categories",
     *     tags={ "categories"},
     *        
     *     @SWG\Parameter(
     *         ref="#/parameters/authentication"
     *     ),
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
     *         @SWG\Schema(ref="#/definitions/category", type="array")
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
     *     path="categories/{id}",
     *     description="Returns a category by id",
     *     summary="Find a category by id",
     *     tags={ "categories"},
     *     
     *     @SWG\Parameter(
     *         description="ID of category to fetch",
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
     *         @SWG\Schema(ref="#/definitions/category")
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
