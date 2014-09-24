<?php namespace DMA\Friends\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateDmaFriendsRewardUserTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('dma_friends_reward_user', function($table)
		{
			$table->increments('id');
			$table->integer('reward_id')->unsigned()->index();
			$table->foreign('reward_id')->references('id')->on('dma_friends_rewards')->onDelete('cascade');
			$table->integer('user_id')->unsigned()->index();
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('dma_friends_reward_user');
	}

}
