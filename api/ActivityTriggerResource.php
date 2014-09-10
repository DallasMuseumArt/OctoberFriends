<?php namespace DMA\Friends\Api;

use Model;

class ActivityTriggerResource extends BaseResource {

	protected $model 	   = '\DMA\Friends\Models\ActivityTriggerType';
	//protected $transformer = '\DMA\Friends\Api\ActivityTriggerTransformer';

}

class ActivityTriggerTransformer extends BaseTransformer {
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
