<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Models\System\System;
use App\Models\Payment\Card;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const STATUS_INCOMPLETE = 2;
    const STATUS_ACTIVE = 0;
    const STATUS_REMOVED = 1;
    const STATUS_PENDING_PROVIDER = 3;

    // emairate_id_front
    // emairate_id_back
    // drive_photo 
    // RTA_card_front
    // RTA_card_back
    // vehicle_registration_form 



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

     public function archive()
    {
        if (empty($this->archive_id)) {
            $this->createArchive();
        }
        return $this->belongsTo(Archive::class, 'archive_id', 'id');
    }

    public function createArchive()
    {
        if($this->archive_id){
            return $this->archive_id;
        }
        $archive = Archive::getWithName("users/$this->id", "($this->id)");
        $this->archive_id = $archive->id;
        $this->save();
        return $this->archive_id;
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class , 'id','user_id');
    }

    public function cards()
    {
        return $this->hasMany(Card::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    
    public function data($type = System::DATA_BRIEF)
    {
        $data = (object)[];
        if($type == System::DATA_BRIEF){
            $data->name = $this->name;
            $data->secret = $this->secret;
            $data->slug = $this->slug;
            
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

   public function verify($type = 'email' , $status = null)
   {
    if($type == 'email'){
        // verify email
        if($this->email == null){
            $this->email = $this->draft_email;
            $this->draft_email = null;
            $this->slug = null;
            $this->email_verification = User::STATUS_ACTIVE;
            $this->save();
        }else{
            $this->draft_email = null;
            $this->slug = null;
            $this->save();
        }
        
    }else if($type == 'mobile'){
        // verify mobile
        if($this->mobile == null){
            $this->mobile = $this->draft_mobile;
            $this->draft_mobile = null;
            $this->otp_code = null;
            $this->otp_time = null;
            $this->status = User::STATUS_ACTIVE;
            $this->save();
        }else{
            $this->otp_code = null;
            $this->otp_time = null;
            $this->save();
        }

        if($status){
            $this->status = User::STATUS_PENDING_PROVIDER;
            $this->save();
        }
       
    }
   }

   public function draft($type = false)
   {
        $this->draft_email = $this->email;
        $this->draft_mobile = $this->mobile;
        $this->email = null;
        $this->mobile = null;
        $this->save();
   }

}
