<?php

namespace App\Services;
use App\Models\User;

class UserService
{
    public function checkFound($data)
    {
        // check if user found 
        $user = User::where('email',$data['email'])->orWhere('mobile',$data['mobile'])->first();
        if($user){
            return true;
        }
        return false;
    }

    public function profile()
    {
        $user =  auth()->user();
        $data = (object)[];
        $data->name = $user->name;
        $data->mobile = $user->mobile;
        $data->email = $user->email;
        $data->secret = $user->secret;

        return $data;


    }
}

?>