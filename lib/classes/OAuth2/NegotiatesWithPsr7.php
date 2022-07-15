<?php

namespace Studip\OAuth2;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Response;
use Trails_Response;

trait NegotiatesWithPsr7
{
    protected function getPsrRequest(): ServerRequestInterface
    {
        return \Slim\Psr7\Factory\ServerRequestFactory::createFromGlobals();
    }

    protected function getPsrResponse(): ResponseInterface
    {
        return new Response();
    }

    protected function convertPsrResponse(ResponseInterface $response): Trails_Response
    {
        $trailsResponse = new Trails_Response((string) $response->getBody(), [], $response->getStatusCode());
        foreach ($response->getHeaders() as $key => $values) {
            foreach ($values as $value) {
                $trailsResponse->add_header($key, $value);
            }
        }

        return $trailsResponse;
    }

    protected function renderPsrResponse(ResponseInterface $response): void
    {
        $this->set_status($response->getStatusCode());
        $this->render_text((string) $response->getBody());
        foreach ($response->getHeaders() as $key => $values) {
            foreach ($values as $value) {
                $this->response->add_header($key, $value);
            }
        }
    }
}
