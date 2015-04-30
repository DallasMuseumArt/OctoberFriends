<?php namespace DMA\Friends\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use DMA\Friends\Classes\LocationManager;
use RainLab\User\Models\User;
use DMA\Friends\Models\Usermeta;
use DMA\Friends\Classes\Notifications\NotificationMessage;
use SystemException;
use Auth;
use App;
use Log;
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
        $barcodeId = get('barcodeId');
        $barcodeId = trim($barcodeId);

        $location = LocationManager::getLocation();

        if (!$location || empty($barcodeId)) {
            //App::abort(403, 'Unauthorized access');
            return Redirect::to('/');
        }

        if ($location->is_authorized) {

            $user = User::where('barcode_id', $barcodeId)->first();

            // Attempt to lookup membership if a user isnt present
            if (!$user) {
                $usermeta = Usermeta::with('user')->where('current_member_number', '=', $barcodeId)->first();
                if (isset($usermeta->user) && !empty($usermeta->user)) {
                    $user = $usermeta->user;
                }
            }

            if (!$user) {
                Log::debug("Failed to login user", ['barcodeId' => $barcodeId, 'user' => $user]);
                // The user does not exist, so flash an error
                Flash::error(Lang::get('dma.friends::lang.app.loginFailed'));
            } else {
                Log::debug("Logged in user", ['barcodeId' => $barcodeId, 'user' => $user]);
                //The user exists so log them in
                Auth::login($user);
                Event::fire('auth.login', [$user]);
            }

        }

        return Redirect::to('/');
    }
}
