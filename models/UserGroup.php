<?php namespace DMA\Friends\Models;

use October\Rain\Auth\Models\Group as GroupBase;

/**
 * Friends User group model
 * @package DMA\Friends\Models
 * @author Carlos Arroyo
 *
 */
class UserGroup extends GroupBase{
	/**
	 * @var string The database table used by the model.
	 */
	protected $table = 'dma_friends_user_groups';
		
	/**
	 * @var array Validation rules
	 */
	public $rules = [
		#'name' => 'required|between:4,16|unique:groups',
	];
	
	/**
	 * @var array Relations
	 */
	public $belongsToMany = [
		'users' => ['RainLab\User\Models\User', 
		'table' => 'dma_friends_users_groups',
		'primaryKey' => 'group_id',
		'foreignKey' => 'user_id',
		]
	];	
	
	/**
	 * @var array Relations
	 */
	public $belongsTo = [
		'owner' => ['RainLab\User\Models\User', 'foreignKey' => 'owner_id']	
	];

}