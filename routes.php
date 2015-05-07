<?php

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
        // History : 
        // 04/08/2015 : Adding Try Catch statement to prevent a fatal exception when acessing 
        // October backend update settings. What happens in there is a race 
        // condition when visiting October backend update page. Plugings don't follow their normal flow it looks like 
        // the boot function is not called, so facades are not created therfore FriendsAPI doesn't exists.
        try {
            DMA\Friends\Facades\FriendsAPI::getRoutes();
        } catch(\ReflectionException $e) {
            // FriendsAPI facade doesn't exist yet
            // Do nothing, just live long and prosper
        }
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

// TODO: add ajax route

Route::get('friends/reports/ajax/{class}', function($class) {
    return \DMA\Friends\Controllers\Ajax::report($class);
});

