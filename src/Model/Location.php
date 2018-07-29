<?php

namespace AloPeyk\Model;

use AloPeyk\AloPeykApiHandler;

class Location
{
    /**
     * @param $latitude
     * @param $longitude
     * @return mixed
     */
    public static function getAddress($latitude, $longitude)
    {
        return AloPeykApiHandler::getAddress($latitude, $longitude);
    }

    /**
     * @param $locationName
     * @return mixed
     */
    public static function getSuggestions($locationName, $latlng)
    {
        return AloPeykApiHandler::getLocationSuggestion($locationName, $latlng);
    }

}
