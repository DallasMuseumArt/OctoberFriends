<?php


Route::group(['prefix' => 'friends/api', 'namespace' => 'DMA\Friends\Api'], function() {

	Route::resource('activity', 				'ActivityResource');
	Route::resource('activity-log', 			'ActivityLogResource');
	Route::resource('activity-trigger-type', 	'ActivityTriggerResource');
	Route::resource('activity-type', 			'ActivityTypeResource');
	Route::resource('badge',		 			'BadgeResource');
	Route::resource('location',		 			'LocationResource');
	Route::resource('reward',		 			'RewardResource');
	Route::resource('step',			 			'StepResource');

});
