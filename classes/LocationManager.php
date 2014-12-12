<?php

namespace DMA\Friends\Classes;

use DMA\Friends\Models\Location;
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

    public static function getLocation()
    {
        $locationId = Session::get('dmafriends.activeLocationId');
        return Location::find($locationId);
    }
}