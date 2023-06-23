<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Models\System\System;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'otp_code',
        'otp_time',
        'mobile',
        'secret'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    
    public function data($type = System::DATA_BRIEF)
    {
        $data = (object)[];
        if($type == System::DATA_BRIEF){
            $data->name = $this->name;
            $data->email = $this->email;
            $data->mobile = $this->mobile;
            $data->secret = $this->secret;
            
        }else if ($type == System::DATA_DETAILS){
            $data->name = $this->name;
            $data->email = $this->email;
            $data->mobile = $this->mobile;
            $data->secret = $this->secret;
            // $data->token = $this->createToken();
            

        }else if ($type == System::DATA_LIST){

        }

        return $data;
    }

    // public function createToken()
    // {
    //     $user = User::find(4);
    //     $token = $user->createToken('My Token')->accessToken;
    //     return '$token->token';
    // }

}
