<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Http\Exception\HttpResponseException;
use Symfony\Component\Debug\Exception\FlattenException;
use Illuminate\Http\Response;
use App\Services\CashMachine\Exceptions\ServiceException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class
        // HttpException::class,
        // ModelNotFoundException::class,
        // ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Exception $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        error_log('================ EXCEPTION REPORT LOG ================');
        error_log('- Message: ' . $e->getMessage());
        error_log('- Exception class: ' . get_class($e));
        error_log('- File: ' . $e->getFile());
        error_log('- Line: ' . $e->getLine());
        error_log('- Stacktrace: ' . $e->getTraceAsString());
        error_log('================ // EXCEPTION REPORT LOG // ================');

        if (env('ERROR_FORMAT', 'json') == 'html') {
            return parent::render($request, $e);
        }

        $status = Response::HTTP_INTERNAL_SERVER_ERROR;

        if ($e instanceof HttpResponseException) {
            $status = Response::HTTP_INTERNAL_SERVER_ERROR;
        } elseif ($e instanceof MethodNotAllowedHttpException) {
            $status = Response::HTTP_METHOD_NOT_ALLOWED;
            $e = new MethodNotAllowedHttpException([], 'HTTP_METHOD_NOT_ALLOWED', $e);
        } elseif ($e instanceof NotFoundHttpException) {
            $status = Response::HTTP_NOT_FOUND;
            $e = new NotFoundHttpException('HTTP_NOT_FOUND', $e);
        } elseif ($e instanceof ModelNotFoundException) {
            $status = Response::HTTP_NOT_FOUND;
            $e = new NotFoundHttpException('HTTP_NOT_FOUND', $e);
        } elseif ($e instanceof AuthorizationException) {
            $status = Response::HTTP_FORBIDDEN;
            $e = new AuthorizationException('HTTP_FORBIDDEN', $status);
        } elseif ($e instanceof \Dotenv\Exception\ValidationException && $e->getResponse()) {
            $status = Response::HTTP_BAD_REQUEST;
            $e = new \Dotenv\Exception\ValidationException('HTTP_BAD_REQUEST', $status, $e);
        } elseif ($e instanceof ValidationException) {
            $status = Response::HTTP_BAD_REQUEST;
            $details = $e->errors();
            $e = new \Dotenv\Exception\ValidationException('HTTP_BAD_REQUEST', $status, $e);
        } elseif ($e instanceof AuthenticationException) {
            $status = Response::HTTP_FORBIDDEN;
            $e = new AuthorizationException('HTTP_FORBIDDEN', $status);
        } elseif ($e instanceof ServiceException) {
            $status = Response::HTTP_BAD_REQUEST;
            $serviceMessage = $e->getMessage();
            $e = new \Dotenv\Exception\ValidationException('HTTP_BAD_REQUEST', $status, $e);
            $details = [
                'service' => $serviceMessage
            ];
        } elseif ($e) {
            $e = new Exception('HTTP_INTERNAL_SERVER_ERROR', $status);
        }

        $fe = FlattenException::create($e, $status, [
            'Content-Type' => 'application/json'
        ]);

        $content = [
            'message' => $fe->getMessage(),
            'status' => $fe->getStatusCode()
        ];

        if (!empty($details)) {
            $content['details'] = $details;
        }

        if (env('APP_ENV') == 'development') {
            $content['file'] = $fe->getFile();
            $content['line'] = $fe->getLine();
            $content['class'] = $fe->getClass();

            if (env('APP_DEBUG', false)) {
                $content['trace'] = $fe->getTrace();
            }
        }

        $response = new Response(json_encode($content), $fe->getStatusCode(), $fe->getHeaders());

        return $response;
    }
}
