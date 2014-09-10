<?php namespace DMA\Friends\Api;

use Model;

class ActivityResource extends BaseResource {

	protected $pageSize    = 2;
	protected $model 	   = '\DMA\Friends\Models\Activity';
	protected $transformer = '\DMA\Friends\Api\ActivityTransformer';

}

class ActivityTransformer extends BaseTransformer {

	/**
	 * List of resources possible to include
	 *
	 * @var array
	 */
	protected $availableIncludes = [
		'steps'
	];	
	
	public function transforms(Model $instance)
	{
		return [
		'id'   => (int) $instance->id,
		'title' => $instance->title,
		'steps' => $instance->steps,
		];
	}
	
	/**
	 * Include Author
	 *
	 * @return League\Fractal\ItemResource
	 */
	public function includeSteps(Model $instance)
	{
		$stpes = $instance->steps;
	
		return $this->item($author, new StepResource);
	}	
	
}
