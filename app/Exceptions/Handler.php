<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\{Auth, Log};
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
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
            // Log all exceptions with context
            if ($this->shouldReport($e)) {
                $userId = Auth::check() ? Auth::user()->id : null;
                Log::error('Exception occurred', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'user_id' => $userId,
                    'ip' => request()->ip(),
                    'url' => request()->url(),
                    'method' => request()->method(),
                ]);
            }
        });

        // Handle ModelNotFoundException (404)
        $this->renderable(function (ModelNotFoundException $e) {
            return response()->view('errors.404', [], 404);
        });

        // Handle ValidationException
        $this->renderable(function (ValidationException $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        });

        // Handle authentication exceptions
        $this->renderable(function (AuthenticationException $e) {
            return redirect()->route('login')
                ->with('error', 'Authentification requise.');
        });

        // Handle all HTTP exceptions
        $this->renderable(function (HttpExceptionInterface $e) {
            $status = $e->getStatusCode();

            if (view()->exists("errors.{$status}")) {
                return response()->view("errors.{$status}", ['exception' => $e], $status);
            }

            return response()->view('errors.generic', ['exception' => $e, 'status' => $status], $status);
        });
    }
}
