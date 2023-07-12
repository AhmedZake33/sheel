<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\requestCreateRequest;
use App\Models\System\System;
use App\Models\Request as RequestModel;
use App\Services\LocationService;

class RequestsController extends Controller
{
    protected $locationService = null;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    public function create(requestCreateRequest $request)
    {
        
        // dd($request->validated());
        $user =  auth()->user();
        $data = $request->validated();
        $data['user_id'] = $user->id;
        // return (Auth::guard('api')->check());
        $requestData = RequestModel::create($data);
        // $data = (object)[];
        // $data->id = 1;
        // $data->lat = '123';
        // $data->lng = '123';
        // $data->photo = "http://downloadimage";

        return success($requestData->data(),System::HTTP_OK,'SUCCESS CREATE REQUEST');
    }

    public function nearestLocations(Request $request)
    {
        $requestModel = RequestModel::findOrFail($request['request']);
        return $this->locationService->getNearestLocations($requestModel->lat , $requestModel->lng);
        $data = (object)[];
        $user = (object)[];
        $user->id = 2;
        $user->name = 'zaki';
        $data->id = 1;
        $data->lat = '123';
        $data->lng = '123';
        $data->user = $user;
        $arr = [$data,$data,$data];
        
        return success($arr,System::HTTP_OK,'SUCCESS');
    }

    public function nearestLocation(Request $request)
    {
        $requestModel = RequestModel::findOrFail($request['request']);
        // first get nearest locations
        $nearestLocations = $this->locationService->getNearestLocations($requestModel->lat , $requestModel->lng);

        // get lowest distance 
        $nearestLocation = $this->locationService->getNearestLocation($requestModel->lat , $requestModel->lng , $nearestLocations);
        return $nearestLocation;
        $data = (object)[];
        $user = (object)[];
        $user->id = 2;
        $user->name = 'zaki';

        $data->id = 1;
        $data->lat = '123';
        $data->lng = '123';
        $data->user = $user;
        
        return success($data,System::HTTP_OK,'SUCCESS');
    }

    public function cancel(Request $request)
    {
        return success([],System::HTTP_OK,'SUCCESS CANCEL REQUEST');
    }

    public function show(Request $request)
    {
        $data = (object)[];
        $data->id = 1;
        $data->lat = '123';
        $data->lng = '123';
        $data->photo = "http://downloadimage";

        return success($data,System::HTTP_OK,'SUCCESS');
    }
}
