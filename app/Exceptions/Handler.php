<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        
        $this->renderable(function (Throwable $e) {
            if($e instanceof AuthenticationException){
                return response()->json(["message" => "You are Not Authorized"] , 401);
            }else if ($e instanceof NotFoundHttpException){
                return response()->json(["message" => "Model Not Found"],404);
            }else{
                return response()->json(["message" =>$e->getMessage()]);
            }
        });
    }
}
