<?php namespace DMA\Friends\Api;

use Model;

class BadgeResource extends BaseResource {

    protected $model        = '\DMA\Friends\Models\Badge';
    //protected $transformer = '\DMA\Friends\Api\BadgeTransformer';

}

class BadgeTransformer extends BaseTransformer {
    /*
    public function transform(Model $instance)
    {
        return [
        'id'   => (int) $instance->id,
        'title' => $instance->title,
        ];
    }
    */
}
