<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReviewRequest;
use Illuminate\Http\Request;
use App\Models\System\System;
use App\Models\Request as RequestModel;
use App\Models\Review;

class ReviewsController extends Controller
{
    public function add(ReviewRequest $request ,$id)
    {
        $requestModel = RequestModel::findOrFail($id); 
        if(!RequestModel::canReview($requestModel->id , auth()->user())){
            return error('there is error',System::HHTP_Unprocessable_Content,'something went wrong');
        }

        // we will create review or update current review

        if($requestModel->review){
            $review = $requestModel->review;
        }else{
            $review = new Review();
        }

        $review->request_id = $requestModel->id;
        $review->user_id = auth()->id();
        $review->rate = $request->rate;
        $review->comment = $request->comment;
        $review->save();
        
        return success([],System::HTTP_OK , 'success add review');
    }
}
