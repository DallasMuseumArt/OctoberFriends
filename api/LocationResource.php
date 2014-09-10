<?php namespace DMA\Friends\Api;

use Model;

class LocationResource extends BaseResource {

	protected $model 	   = '\DMA\Friends\Models\Location';
	//protected $transformer = '\DMA\Friends\Api\LocationTransformer';

}

class LocationTransformer extends BaseTransformer {
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
