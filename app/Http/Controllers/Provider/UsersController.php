<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\registerProvider;
use App\Services\ProviderService;

class UsersController extends Controller 
{

    protected $provider = null;

    public function __construct(ProviderService $provider)
    {
        $this->provider = $provider;
    }
    public function regsiterProvider(registerProvider $request)
    {
        return $this->provider->createProvider($request);
        return 'success';
    }
}