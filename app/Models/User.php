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

    const STATUS_INCOMPLETE = 2;
    const STATUS_ACTIVE = 0;
    const STATUS_REMOVED = 1;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'type',
        'password',
        'otp_code',
        'otp_time',
        'mobile',
        'secret',
        'draft_email',
        'draft_mobile'
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

    public function provider()
    {
        return $this->belongsTo(Provider::class , 'id','user_id');
    }

    
    public function data($type = System::DATA_BRIEF)
    {
        $data = (object)[];
        if($type == System::DATA_BRIEF){
            $data->name = $this->name;
            $data->secret = $this->secret;
            
        }else if ($type == System::DATA_DETAILS){
            $data->name = $this->name;
            $data->email = $this->email;
            $data->mobile = $this->mobile;
            $data->secret = $this->secret;
        }else if ($type == System::DATA_LIST){

        }

        return $data;
    }

   public static function createOtp($user,$slug = false)
   {
        if($slug){
            $user->slug = rand(10000,99999);
        }
        $otp = rand(00000,00000);
        $user->otp_code = 11111;
        $user->otp_time = now();
        $user->save();

        // send to user
   }

   public function verify($type = 'email')
   {
    if($type == 'email'){
        // verify email
        $this->email = $this->draft_email;
        $this->draft_email = null;
        $this->slug = null;
        $this->email_verification = User::STATUS_ACTIVE;
        $this->save();
    }else if($type == 'mobile'){
        // verify mobile
        $this->mobile = $this->draft_mobile;
        $this->draft_mobile = null;
        $this->otp_code = null;
        $this->otp_time = null;
        $this->status = User::STATUS_ACTIVE;
        $this->save();
    }
   }

   public function remove($type = false)
   {
        $this->draft_email = $this->email;
        $this->draft_mobile = $this->mobile;
        $this->email = null;
        $this->mobile = null;
        $this->save();
   }

}
