<?php namespace DMA\Friends\Api;

use Model;

class StepResource extends BaseResource {

	protected $model 	   = '\DMA\Friends\Models\Step';
	//protected $transformer = '\DMA\Friends\Api\StepTransformer';

}

class StepTransformer extends BaseTransformer {
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
