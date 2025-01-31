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
        return Review::createReview($request , $id);
    }
}
