<?php namespace DMA\Friends\API\Resources;

use DMA\Friends\Classes\API\BaseResource;

class LocationResource extends BaseResource {

    protected $model        = '\DMA\Friends\Models\Location';

    protected $transformer  = '\DMA\Friends\API\Transformers\LocationTransformer';

}
