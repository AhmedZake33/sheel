<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use App\Models\System\System;
use App\Models\Payments\Card;
use DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const STATUS_INCOMPLETE = 2;
    const STATUS_ACTIVE = 0;
    const STATUS_REMOVED = 1;
    const STATUS_PENDING_PROVIDER = 3;

    const TYPE_USER = 1;
    const TYPE_PROVIDER = 2;
    const TYPE_ADMIN = 3;

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
        'mobile_code',
        'secret',
        'draft_email',
        'draft_mobile',
        'status'
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

    public function cards($deatils = null)
    {
        $query =  $this->hasMany(Card::class);
        if($deatils == null){
            return $query;
        }else{
            return $query->select('id','first_six','last_four')->get();
        }
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    
    public function data($type = System::DATA_BRIEF)
    {
        $data = (object)[];
        $data->active = ($this->status == User::STATUS_PENDING_PROVIDER)? false : true;
        if($type == System::DATA_BRIEF){
            $data->name = $this->name;
            $data->secret = $this->secret;
            $data->slug = $this->slug;
            
        }else if ($type == System::DATA_DETAILS){
            $data->name = $this->name;
            $data->email = $this->email;
            $data->mobile = $this->mobile;
            $data->secret = $this->secret;
            $data->mobile_code = $this->mobile_code;
        }else if ($type == System::DATA_LIST){
            $data->provider = $this->provider;
        }else if($type == system::DATA_ORIGINAL){
            $data->id = $this->id;
            $data->name = $this->name;
            $data->email = $this->email;
            $data->mobile = $this->mobile;
            $data->mobile_code = $this->mobile_code;
            $data->profile_picture = $this->profilePicture();
        }

        return $data;
    }

    public function profilePicture()
    {
        if($this->archive){
            if(count($this->archive->children()->where('short_name','profile_photo')->pluck('id'))){
                return route('download_file', $this->archive->children()->where('short_name','profile_photo')->pluck('id')[0]);
            }
        }
        return null;
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

   public function chats()
   {
        return $this->hasMany(Chat::class);
   }

   public function files($type = User::TYPE_USER)
    {
        $data = [];
        
        if($type == User::TYPE_USER){
            // $data->photo = count($this->archive->children()->where('short_name','profile_photo')->pluck('id')) ? route('download_file', $this->archive->children()->where('short_name','profile_photo')->pluck('id')[0]) : null;

        }else if($type == User::TYPE_PROVIDER){
            // PHOTOS

            // 1- vehicle_registration_form
            $vehicle_registration_form  = (object)[];
            $vehicle_registration_form->key = "vehicle Registration Form";
            $vehicle_registration_form->value = count($this->archive->children()->where('short_name','vehicle_registration_form')->pluck('id')) ? route('download_file', $this->archive->children()->where('short_name','vehicle_registration_form')->pluck('id')[0]) : null;
            // $data->vehicle_registration_form = $vehicle_registration_form;
            array_push($data ,$vehicle_registration_form);

            // 1- RTA_card_back
            $RTA_card_back  = (object)[];
            $RTA_card_back->key = "RTA Card Back";
            $RTA_card_back->value = count($this->archive->children()->where('short_name','RTA_card_back')->pluck('id')) ? route('download_file', $this->archive->children()->where('short_name','RTA_card_back')->pluck('id')[0]) : null;
            // $data->RTA_card_back = $RTA_card_back;
            array_push($data ,$RTA_card_back);


            // 1- RTA_card_front
            $RTA_card_front  = (object)[];
            $RTA_card_front->key = "RTA Card Front";
            $RTA_card_front->value = count($this->archive->children()->where('short_name','RTA_card_front')->pluck('id')) ? route('download_file', $this->archive->children()->where('short_name','RTA_card_front')->pluck('id')[0]) : null;
            // $data->RTA_card_front = $RTA_card_front;
            array_push($data ,$RTA_card_front);


            // 1- drive_photo
            $drive_photo  = (object)[];
            $drive_photo->key = "Drive Photo";
            $drive_photo->value = count($this->archive->children()->where('short_name','drive_photo')->pluck('id')) ? route('download_file', $this->archive->children()->where('short_name','drive_photo')->pluck('id')[0]) : null;
            // $data->drive_photo = $drive_photo;
            array_push($data ,$drive_photo);

            // 1- emairate_id_back
            $emairate_id_back = (object)[];
            $emairate_id_back->key = "Emairate ID Back";
            $emairate_id_back->value = count($this->archive->children()->where('short_name','emairate_id_back')->pluck('id')) ? route('download_file', $this->archive->children()->where('short_name','emairate_id_back')->pluck('id')[0]) : null;
            // $data->emairate_id_back = $emairate_id_back;
            array_push($data ,$emairate_id_back);

            // 1- emairate_id_front
            $emairate_id_front = (object)[];
            $emairate_id_front->key = "Emairate ID Front";
            $emairate_id_front->value =  count($this->archive->children()->where('short_name','emairate_id_front')->pluck('id')) ? route('download_file', $this->archive->children()->where('short_name','emairate_id_front')->pluck('id')[0]) : null;
            // $data->emairate_id_front = $emairate_id_front;
            array_push($data ,$emairate_id_front);
        }
       
        return $data;

    }

    public function calculateReview()
    {
       $query = DB::table('reviews')->where("reviewable_id",$this->id)->selectRaw("AVG(rate) as rate")->select("rate")->first();
       return $query ? $query->rate : null;
    }

}
