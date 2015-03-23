<?php namespace DMA\Friends\API\Resources;

use DMA\Friends\Classes\API\BaseResource;

class RewardResource extends BaseResource {

    protected $model        = '\DMA\Friends\Models\Reward';

    protected $transformer  = '\DMA\Friends\API\Transformers\RewardTransformer';

}
