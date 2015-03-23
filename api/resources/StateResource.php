<?php namespace DMA\Friends\API\Resources;


use DMA\Friends\Classes\API\BaseResource;

class StateResource extends BaseResource
{
    protected $model        = '\RainLab\User\Models\State';
    
    protected $transformer  = '\DMA\Friends\API\Transformers\StateTransformer';
        
}