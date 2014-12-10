<?php namespace DMA\Friends\Api;

use Model;

class CategoryResource extends BaseResource {

    protected $model        = '\DMA\Friends\Models\Category';
    //protected $transformer = '\DMA\Friends\Api\CategoryTransformer';

}

class CategoryTransformer extends BaseTransformer {
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
