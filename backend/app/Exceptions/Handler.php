<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Force JSON error responses for the public API, regardless of the request's
     * Accept header, so clients always get a predictable body shape:
     *   404 -> {"message": "..."}      422 -> {"message": "...", "errors": {...}}
     */
    protected function shouldReturnJson($request, Throwable $e): bool
    {
        return $request->is('api/*') || parent::shouldReturnJson($request, $e);
    }

    /**
     * Keep the not-found body generic — never leak the model class name.
     */
    protected function prepareJsonResponse($request, Throwable $e)
    {
        if ($request->is('api/*') && $e instanceof NotFoundHttpException) {
            return response()->json(['message' => 'Resource not found.'], 404);
        }

        return parent::prepareJsonResponse($request, $e);
    }
}
