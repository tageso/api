<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Response;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        $response = parent::render($request, $exception);

        $response->setStatusCode(500);
        $data = [
            "msg" => $exception->getMessage(),
            "code" => $exception->getCode()
        ];

        if (is_a($exception, \App\Exceptions\HTTPException::class)) {
            Log::info("HTTP Exception");
            $response->setStatusCode($exception->getHttpCode());
            $data["httpCode"] = $exception->getHttpCode();
            $data["typ"] = "http";
        } elseif (is_a($exception, NotFoundHttpException::class)) {
            Log::warning("API-Endpoint not found");
            $data["typ"] = "exception";
            $data["msg"] = "API Endpoint not found";
            $data["httpCode"] = 500;
        } elseif (is_a($exception, "Illuminate\Validation\ValidationException")) {
            Log::info("Validation exception");
            $response->setStatusCode(400);
            $data["validation"] = $exception->errors();
            $data["httpCode"] = 400;
            $data["typ"] = "validation";
        } else {
            Log::error("Unhandelt exception!");
            $data["tech_msg"] = $data["msg"];
            $data["msg"] = "Es ist ein interner Fehler aufgetreten, bitte informiere den Administrator";
            $data["typ"] = "exception";
            $data["exception"] = get_class($exception);
            $data["line"] = $exception->getLine();
            $data["file"] = $exception->getFile();
        }

        #$data["msg"] = $data["msg"] . "<br>Request-ID for Administrator: ".app()->requestid;

        /*if(is_a($exception, \App\Exceptions\DataMissingException::class)){
            $response->setStatusCode($exception->getHttpCode());
            $data["httpCode"] = $exception->getHttpCode();
            $data["typ"] = "dataMissing";
            $data["techCode"] = $exception->techCode;
            $data["missingInput"] = $exception->getMissingInputField();
        }*/


        if ($request->header("accept") == "application/json") {
            $response->setContent(\GuzzleHttp\json_encode($data));
        }

        return $response;
    }
}
