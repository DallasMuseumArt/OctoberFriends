<?php namespace DMA\Friends\API\Resources;

use Request;
use DMA\Friends\Classes\API\BaseResource;

class CountryResource extends BaseResource
{
    protected $model        = '\RainLab\User\Models\Country';
    
    protected $transformer  = '\DMA\Friends\API\Transformers\CountryTransformer';

    /**
     * @SWG\Get(
     *     path="countries",
     *     description="Returns all countries",
     *     tags={ "countries"},
     *
     *     @SWG\Response(
     *         response=200,
     *         description="Successful response",
     *         @SWG\Schema(ref="#/definitions/country", type="array")
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
     *     path="countries/{id}",
     *     description="Returns a country by id",
     *     tags={ "countries"},
     *
     *     @SWG\Parameter(
     *         description="ID of country to fetch",
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
     *         @SWG\Schema(ref="#/definitions/country")
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