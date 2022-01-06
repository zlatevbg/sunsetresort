<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;

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
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof \Illuminate\Session\TokenMismatchException) {
            // redirect back with error message does not work when session is expired, because the session is regenerated 2 times.
            // instead I've overwritten the Illuminate\Foundation\Http\Middleware\VerifyCsrfToken handle method in the VerifyCsrfToken middleware.
        }

        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        if ($e instanceof FileNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        if ($e instanceof \ErrorException) { // fatal php exceptions
            $response = new Response('', 500);
            $status = $response->getStatusCode();
            $statusText = \Illuminate\Http\Response::$statusTexts[$response->getStatusCode()];

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $status . ' ' . $statusText . '<br>' . $e->getMessage(), 'preventRetry' => true], $status); // preventRetry is for FineUploader
            } else {
                $content = view('errors.500', compact('status', 'statusText'))->with('message', $e->getMessage())->render();
                $response->setContent($content);
                return $response;
            }
        }

        if ($e instanceof \PDOException) { // db exceptions
            $response = new Response('', 503);
            $status = $response->getStatusCode();
            $statusText = \Illuminate\Http\Response::$statusTexts[$response->getStatusCode()];

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => $status . ' ' . $statusText . '<br>' . $e->getMessage(), 'preventRetry' => true], $status); // preventRetry is for FineUploader
            } else {
                $content = view('errors.db', compact('status', 'statusText'))->with('message', $e->getMessage())->render();
                $response->setContent($content);
            }

            if ($e instanceof \Illuminate\Database\QueryException) {
                return $response;
            } else {
                $response->sendHeaders();
                die ($response->getContent());
                // return $response; // returns the content twice
            }
        }

        return parent::render($request, $e);
    }
}
