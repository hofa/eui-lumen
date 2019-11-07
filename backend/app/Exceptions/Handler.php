<?php

namespace App\Exceptions;

use App\Exceptions\ValidatorException;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        MethodNotAllowedHttpException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Exception $exception)
    {
        if (method_exists($exception, 'getStatusCode')) {
            return $this->output($exception, $exception->getStatusCode());
        }

        if ($exception instanceof AuthorizationException) {
            return $this->output($exception, $exception->getCode());
        }

        if ($exception instanceof ValidationException) {
            return $this->output($exception, 422, $exception->validator->errors()->toArray());
        }

        if ($exception instanceof ValidatorException) {
            return $this->output($exception, 422, ValidatorException::getError());
        }

        return $this->output($exception, 500);
        return parent::render($request, $exception);
    }

    public function output($exception, $code = 0, $errors = [])
    {
        // $code = $code == 0 && method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : $code;
        $httpCode = config('options.httpCode');
        $msg = $exception->getMessage();
        $msg = empty($msg) ? ($httpCode[$code] ?? $code) : $msg;
        $output = [
            // 'code' => $code,
            'meta' => ['message' => $msg],
        ];
        if (!empty($errors)) {
            $output['errors'] = $errors;
        }
        if (config('app.debug')) {
            $output['except'] = $this->convertExceptionToArray($exception);
        }
        $output = json_encode($output, JSON_UNESCAPED_UNICODE);
        $code = $code == 0 ? 500 : $code;
        return response($output, $code)->withHeaders(['Content-Type' => 'application/json']);
    }

}
