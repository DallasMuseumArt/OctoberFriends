<?php namespace DMA\Friends\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use DMA\Friends\Classes\LocationManager;
use RainLab\User\Models\User;
use DMA\Friends\Models\Usermeta;
use DMA\Friends\Classes\AuthManager;
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
            return Redirect::to('/');
        }

        if ($location->is_authorized) {

            $data = [
                'login'         => $barcodeId,
                'no_password'   => true,
            ];

            AuthManager::auth($data);

        }

        return Redirect::to('/');
    }
}
