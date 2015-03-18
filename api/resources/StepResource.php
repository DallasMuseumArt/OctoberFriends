<?php namespace DMA\Friends\API\Resources;

use DMA\Friends\Classes\API\BaseResource;

class StepResource extends BaseResource {

    //protected $pageSize     = 0;
    protected $model        = '\DMA\Friends\Models\Step';

    protected $transformer  = '\DMA\Friends\API\Transformers\StepTransformer';

}
