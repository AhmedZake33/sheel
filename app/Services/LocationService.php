<?php
namespace App\Services;
use App\Models\Provider;
use App\Models\User;


class LocationService 
{
    public function getNearestLocations($lat , $lng)
    {
        $distance = env('distance');
        $minLat = $lat - rad2deg($distance / 6371);
       
        $maxLat = $lat + rad2deg($distance / 6371);
        $minLng = $lng - rad2deg(asin($distance / (6371 * cos(deg2rad($lat)))));
        $maxLng = $lng - rad2deg(asin($distance / (6371 * cos(deg2rad($lat)))));

        $locations = Provider::with('user:id,name,email,mobile')->whereBetween('lat', [$minLat, $maxLat])
        ->whereBetween('lng', [$minLng, $maxLng])
        ->get();

        return $locations;

    }

    public function getNearestLocation($lat , $lng , $nearestLocations)
    {
        if($nearestLocations && count($nearestLocations) > 0){
            $distances = [];
            foreach($nearestLocations as $location)
            {
                // calc distance between location and (lat , lng)
                $distance = $this->calcDistance($lat , $lng , $location->lat , $location->lng);
                array_push($distances , $distance);
            }

            if($distances && count($distances) > 0){
                $index = array_search(min($distances), $distances);
                return $nearestLocations[$index];
            }
        }
    }

    public function calcDistance($lat1 , $lng1 , $lat2 , $lng2)
    {
        $theta = $lng1 - $lng2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist); 
        $dist = rad2deg($dist); 
        $miles = $dist * 60 * 1.1515;
        return $miles * 1.60934;
    }
}

?>