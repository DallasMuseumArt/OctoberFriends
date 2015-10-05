<?php namespace DMA\Friends\API\Resources;


use DMA\Friends\Classes\API\BaseResource;

class StateResource extends BaseResource
{
    protected $model        = '\RainLab\User\Models\State';
    
    protected $transformer  = '\DMA\Friends\API\Transformers\StateTransformer';

    /**
     *  @SWG\Parameter(
     *    parameter="country_id",
     *    description="ID of country to fetch",
     *    format="int64",
     *    in="path",
     *    name="id",
     *    required=true,
     *    type="integer"
     * ),
     * 
     * @SWG\Get(
     *     path="countries/{id}/states",
     *     description="Returns all states of given country",
     *     summary="Return all states by country",
     *     tags={ "countries"},
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
     *    
     *    @SWG\Parameter(
     *         ref="#/parameters/country_id"
     *    ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/state", type="array")
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
     *     path="countires/{id}/states/{state_id}",
     *     description="Returns a state by id",
     *     summary="Find a state id", 
     *     tags={ "countries"},
     *     
     *     @SWG\Parameter(
     *         ref="#/parameters/country_id"
     *     ),
     *     
     *     @SWG\Parameter(
     *         description="ID of state to fetch",
     *         format="int64",
     *         in="path",
     *         name="state_id",
     *         required=true,
     *         type="integer"
     *     ),
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/state")
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