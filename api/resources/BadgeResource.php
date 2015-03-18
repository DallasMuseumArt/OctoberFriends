<?php namespace DMA\Friends\API\Resources;

use DMA\Friends\Classes\API\BaseResource;

class BadgeResource extends BaseResource {

    protected $model        = '\DMA\Friends\Models\Badge';

    protected $transformer  = '\DMA\Friends\API\Transformers\BadgeTransformer';

}
