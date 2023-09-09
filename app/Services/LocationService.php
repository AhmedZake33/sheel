<?php
namespace App\Services;
use App\Models\Provider;
use App\Models\User;


class LocationService 
{
    public function getNearestLocations($requestModel , $providers = [])
    {
        // return $requestModel;
        $distance = env('distance'); 
        $minLat = $requestModel->current_lat - rad2deg($distance / 6371);
       
        $maxLat = $requestModel->current_lat + rad2deg($distance / 6371);
        $minLng = $requestModel->current_lng - rad2deg(asin($distance / (6371 * cos(deg2rad($requestModel->current_lat)))));
        $maxLng = $requestModel->current_lng + rad2deg(asin($distance / (6371 * cos(deg2rad($requestModel->current_lat)))));

        $locations = Provider::with('user:id,name,email,mobile')
        ->where('service_id',$requestModel->service_id)
        ->whereBetween('lat', [$minLat, $maxLat])
        ->whereBetween('lng', [$minLng, $maxLng])
        ->where('user_id','!=',$requestModel->user_id);

        if(count($providers) > 0){
            $locations = $locations->whereNotIn('user_id',$providers);
        }
        $locations = $locations->get()->transform(function($location) use ($requestModel){
            $location->distance = $this->calcDistance($requestModel->current_lat , $requestModel->current_lng , $location->lat , $location->lng);
            return $location;
        });
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