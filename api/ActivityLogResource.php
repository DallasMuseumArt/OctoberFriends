<?php namespace DMA\Friends\Api;

use Model;

class ActivityLogResource extends BaseResource {

	protected $model 	   = '\DMA\Friends\Models\ActivityLog';
	//protected $transformer = '\DMA\Friends\Api\ActivityLogTransformer';

}

class ActivityLogTransformer extends BaseTransformer {
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
