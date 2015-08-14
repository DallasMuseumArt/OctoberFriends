<?php

namespace DMA\Friends\Classes;

use DMA\Friends\Models\Location;
use DMA\Friends\Models\Settings as FriendsSettings;
use Session;

class LocationManager
{

    public function __construct($uuid)
    {
        $location = Location::findByUUID($uuid)->first();

        if (!$location) {
            $location = new Location(['uuid' => $uuid]);
            $location->save();
        }

        $this->location = $location;

        Session::put('dmafriends.activeLocationId', $location->id);
    }

    /**
     * Returns the current kiosk location
     * 
     * @return Location $location
     * return the current registered location
     */
    public static function getLocation()
    {
        $locationId = Session::get('dmafriends.activeLocationId');

        if (!$locationId) {
            $uuid = $_SERVER['HTTP_X_DEVICE_UUID'];
 
            return Location::findByUUID($uuid)->first();
        }

        return Location::find($locationId);
    }

    /**
     * Certain functionality can be disabled if it is not a kiosk.  If kiosk
     * registration is required then functionality can be disabled
     *
     * @return booelan $showAction
     * if true then kiosk location should be registered to enable functionality
     *
     */
    public static function enableAction()
    {
        $require_location = FriendsSettings::get('require_location', false);

        if ($require_location) {
            $location = self::getLocation();
            $showAction = ($location) ? true : false;
        } else {
            $showAction = true;
        }   

        return $showAction;
    }
}
