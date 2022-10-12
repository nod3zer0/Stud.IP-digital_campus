<?php

namespace JsonApi\Errors;

use Neomerx\JsonApi\Exceptions\JsonApiException;
use Neomerx\JsonApi\Schema\Error;
use Neomerx\JsonApi\Schema\ErrorCollection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Exception\HttpException;
use Throwable;

class ErrorHandler
{
    /** @var \Slim\App */
    private $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails,
        ?LoggerInterface $logger = null
    ): ResponseInterface {
        if ($logger) {
            $logger->error($exception->getMessage());
        }

        $response = $this->app->getResponseFactory()->createResponse();
        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write($this->determinePayload($exception, $displayErrorDetails));

        $code = $this->determineStatusCode($exception, $request->getMethod());

        return $response->withStatus($code);
    }

    protected function determineStatusCode(Throwable $exception, string $method): int
    {
        if ('OPTIONS' === $method) {
            return 200;
        }

        if ($exception instanceof HttpException) {
            return $exception->getCode();
        }

        if ($exception instanceof JsonApiException) {
            return $exception->getHttpCode();
        }

        return 500;
    }

    protected function determinePayload(Throwable $exception, bool $displayErrorDetails): string
    {
        $message = '';
        if ($exception instanceof JsonApiException) {
            $httpCode = $exception->getHttpCode();
            $errors = new ErrorCollection();
            foreach ($exception->getErrors() as $error) {
                $errors[] = $this->copyError($error, $displayErrorDetails, $exception);
            }
        } elseif ($exception instanceof HttpException) {
            $errors = $this->createErrorCollection(
                $exception->getCode(),
                $exception->getMessage(),
                $exception->getDescription(),
                $exception,
                $displayErrorDetails
            );
        } else {
            $errors = $this->createErrorCollection(
                '500',
                $exception->getMessage(),
                null,
                $exception,
                $displayErrorDetails
            );
        }

        if (sizeof($errors)) {
            $encoder = $this->app->getContainer()->get('json-api-error-encoder');

            return $encoder->encodeErrors($errors);
        }

        return '';
    }

    private function createErrorCollection(
        string $httpCode,
        string $message,
        ?string $details,
        Throwable $exception,
        bool $displayErrorDetails
    ): ErrorCollection {
        /** @var \Exception $exception */
        $errors = new ErrorCollection();
        $errors->add(
            new Error(
                null,
                null,
                null,
                $httpCode,
                null,
                $message,
                $details,
                $displayErrorDetails ? ['backtrace' => explode("\n", $exception->getTraceAsString())] : null
            )
        );

        return $errors;
    }

    private function copyError(Error $error, bool $displayErrorDetails, JsonApiException $exception): Error
    {
        $newError = new Error(
            $error->getId(),
            $error->getLinks(),
            $error->getTypeLinks(),
            $error->getStatus(),
            $error->getCode(),
            $error->getTitle(),
            $error->getDetail(),
            $displayErrorDetails ? ['backtrace' => explode("\n", $exception->getTraceAsString())] : null,
            false,
            $error->getMeta()
        );

        return $newError;
    }
}
