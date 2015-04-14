<?php namespace DMA\Friends\API\Resources;

use Request;
use DMA\Friends\Classes\API\BaseResource;

class CountryResource extends BaseResource
{
    protected $model        = '\RainLab\User\Models\Country';
    
    protected $transformer  = '\DMA\Friends\API\Transformers\CountryTransformer';

    
}