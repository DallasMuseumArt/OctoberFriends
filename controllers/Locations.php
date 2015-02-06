<?php namespace DMA\Friends\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use DMA\Friends\Classes\LocationManager;
use RainLab\User\Models\User;
use DMA\Friends\Models\Usermeta;
use Auth;
use App;
use Flash;
use Lang;
use Redirect;
use Event;

/**
 * Locations Back-end Controller
 */
class Locations extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('DMA.Friends', 'friends', 'locations');
    }

    /**
     * Resource to login user via barcode scanner for authorized kiosks
     */
    public static function barcodeLogin()
    {
        $barcodeId = post('barcodeId');
        $location = LocationManager::getLocation();

        if (!$location || empty($barcodeId)) {
            App::abort(403, 'Unauthorized access');
            return;
        }
        
        if ($location->is_authorized) {
            $user = User::where('barcode_id', $barcodeId)->first();

            // Attempt to lookup membership if a user isnt present
            if (!$user) {
                $usermeta = Usermeta::where('current_member_number', $barcodeId)->first();
                $user = $usermeta->user;
            }

            if (!$user) {
                // The user does not exist, so flash an error
                Flash::error(Lang::get('dma.friends::lang.app.loginFailed'));
            } else {
                //The user exists so log them in
                Auth::login($user);
                Event::fire('auth.login', [$user]);
            }

        }

        return Redirect::to('/');
    }
}