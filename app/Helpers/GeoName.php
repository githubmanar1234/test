<?php


namespace App\Helpers;


class GeoName
{
    public static function getLocationInfo($lat,$lang){


        $url = 'http://api.geonames.org/countryCodeJSON?lat='.$lat.'&lng='.$lang.'&username=merigla';
        //$url = 'http://maps.google.com/maps/api/geocode/json?address=' . $geoAddress .'&sensor=false';
        $get     = file_get_contents($url);
        $geoData = json_decode($get);
        return $geoData;
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid geocoding results');
        }

        if(isset($geoData->results[0])) {
            foreach($geoData->results[0]->address_components as $addressComponent) {
                if(in_array('administrative_area_level_2', $addressComponent->types)) {
                    return $addressComponent->long_name;
                }
            }
        }
    }
}