<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Request as RequestModel;
use App\Models\System\System;

class Review extends Model
{
    use HasFactory;

    public static function createReview($request , $id)
    {
        $requestModel = RequestModel::findOrFail($id); 
        if(!RequestModel::canReview($requestModel->id , auth()->user())){
            return error('there is error',System::HHTP_Unprocessable_Content,'something went wrong');
        }

        // we will create review or update current review

        if(count($requestModel->reviews)){
            $review = $requestModel->reviews()->where("user_id",auth()->id())->first();
        }else{
            $review = new Review();
        }

        $review->request_id = $requestModel->id;
        $review->user_id = auth()->id();
        $review->rate = $request->rate;
        $review->comment = $request->comment;
        $review->reviewable_id = $requestModel->getReceivingUser();
        $review->save();
        
        return success([],System::HTTP_OK , 'success add review');
    }
}
