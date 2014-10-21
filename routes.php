<?php


Route::group(['prefix' => 'friends/api', 'namespace' => 'DMA\Friends\Api'], function() {

	Route::resource('activity', 				'ActivityResource');
	Route::resource('activity-log', 			'ActivityLogResource');
    Route::resource('category',                 'Category');
	Route::resource('badge',		 			'BadgeResource');
	Route::resource('location',		 			'LocationResource');
	Route::resource('reward',		 			'RewardResource');
	Route::resource('step',			 			'StepResource');

});
