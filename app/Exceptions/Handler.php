<?php

namespace App\Exceptions;

use App\Helpers\JsonResponse;
use App\Helpers\ResponseStatus;
use Firebase\Auth\Token\Exception\ExpiredToken;
use Firebase\Auth\Token\Exception\InvalidToken;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Exception\InvalidArgumentException;
use Kreait\Firebase\Exception\Messaging\AuthenticationError;
use Kreait\Firebase\Exception\Messaging\InvalidMessage;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
//        GeneralException::class,
//        InvalidMessage::class,
//        QueryException::class,
//        ExpiredToken::class,
//        InvalidArgumentException::class,
//        AuthenticationError::class,
//        InvalidToken::class
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
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
        $this->reportable(function (Throwable $e) {

        });
    }

    public function report(Throwable $exception)
    {
        if ($this->shouldReport($exception) && app()->bound('sentry')) {
            app('sentry')->captureException($exception);
        }

        parent::report($exception);
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthorizationException) {
            return JsonResponse::respondError(JsonResponse::MSG_NOT_AUTHORIZED, ResponseStatus::NOT_AUTHORIZED);
        }
        if ($exception instanceof MethodNotAllowedHttpException) {
            return JsonResponse::respondError(trans('responses.msg_not_allowed'), ResponseStatus::NOT_ALLOWED);
        }
        if ($exception instanceof NotFoundHttpException) {
            return JsonResponse::respondError(trans('responses.msg_not_found'), ResponseStatus::NOT_FOUND);

        }
        if ($exception instanceof ModelNotFoundException) {
            return JsonResponse::respondError(trans('responses.msg_not_found'), ResponseStatus::NOT_FOUND);
        }
        if ($exception instanceof GeneralException) {
            Log::debug($exception->getMessage());
            return JsonResponse::respondError($exception->getMessage(), ResponseStatus::BAD_REQUEST);
        }

        if ($exception instanceof AuthenticationException) {
            return JsonResponse::respondError(JsonResponse::MSG_NOT_AUTHENTICATED, ResponseStatus::NOT_AUTHENTICATED);
        }
        if ($exception instanceof \Exception) {
            Log::debug($exception->getMessage());
            return JsonResponse::respondError($exception->getMessage(), ResponseStatus::BAD_REQUEST);
        }
        return parent::render($request, $exception);
    }
}
