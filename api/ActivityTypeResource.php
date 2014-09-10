<?php namespace DMA\Friends\Api;

use Model;

class ActivityTypeResource extends BaseResource {

	protected $model 	   = '\DMA\Friends\Models\ActivityType';
	//protected $transformer = '\DMA\Friends\Api\ActivityTypeTransformer';

}

class ActivityTypeTransformer extends BaseTransformer {
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
