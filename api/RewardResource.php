<?php namespace DMA\Friends\Api;

use Model;

class RewardResource extends BaseResource {

	protected $model 	   = '\DMA\Friends\Models\Reward';
	//protected $transformer = '\DMA\Friends\Api\RewardTransformer';

}

class RewardTransformer extends BaseTransformer {
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
