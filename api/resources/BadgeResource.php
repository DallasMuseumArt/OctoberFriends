<?php namespace DMA\Friends\API\Resources;

use DMA\Friends\Classes\API\BaseResource;

class BadgeResource extends BaseResource {

    protected $model        = '\DMA\Friends\Models\Badge';

    protected $transformer  = '\DMA\Friends\API\Transformers\BadgeTransformer';

    /**
     * @SWG\Get(
     *     path="badges",
     *     description="Returns all badges",
     *     summary="Return all badges",
     *     tags={ "badges"},
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
     *         @SWG\Schema(ref="#/definitions/badge", type="array")
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
     *     path="badges/{id}",
     *     description="Returns a badge by id",
     *     summary="Find a badge by id",
     *     tags={ "badges"},
     *
     *     @SWG\Parameter(
     *         description="ID of badge to fetch",
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
     *         @SWG\Schema(ref="#/definitions/badge")
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
