<?php

use DMA\Friends\Facades\FriendsAPI;
/**
 * Provide custom routes outside of what october provides
 *
 * @package DMA\Friends
 * @author Kristen Arnold, Carlos Arroyo
 */

// Register API routers after Pluging is booted and Laravel is ready
App::before(function($request, $response){

    Route::group(['prefix' => 'friends/api'], function() {
        // Register API Routes
        FriendsAPI::getRoutes();
    });
});


Route::get('logout', function()
{
    Auth::logout();
    return Redirect::to('/');
});

Route::get('location/barcode-login', function() {
	return DMA\Friends\Controllers\Locations::barcodeLogin();
});

Route::group(['prefix'=>'webhooks'], function(){
	// Implement here each individual webhook
	Route::post('twilio/sms', function()
	{
		$request = Input::all();
		$ch = Postman::getChannelInstance('sms');
		return $ch->webhook($request);
	});
});


