<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
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
        //
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Log del error para debugging
        Log::error('Error capturado', [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'url' => $request->fullUrl(),
            'user_id' => auth()->id()
        ]);

        // No mostrar página de mantenimiento para 404
        if ($exception instanceof NotFoundHttpException) {
            return parent::render($request, $exception);
        }

        // Si es una petición AJAX, devolver JSON
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Error del servidor. Intente nuevamente.'
            ], 500);
        }

        // Para cualquier otro error, mostrar página de mantenimiento
        if (app()->environment('local')) {
            return response()->view('errors.maintenance', [
                'message' => 'Estamos experimentando problemas técnicos temporales'
            ], 500);
        }

        // En desarrollo, mostrar el error normal
        return parent::render($request, $exception);
    }
}
