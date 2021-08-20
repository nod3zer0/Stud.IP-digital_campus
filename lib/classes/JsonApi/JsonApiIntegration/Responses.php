<?php

namespace JsonApi\JsonApiIntegration;

use Neomerx\JsonApi\Http\BaseResponses;
use Neomerx\JsonApi\Contracts\Encoder\EncoderInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\MediaTypeInterface;
use Slim\Psr7\Headers;
use Slim\Psr7\Response;

use Neomerx\JsonApi\Contracts\Encoder\Parameters\EncodingParametersInterface;
use Neomerx\JsonApi\Contracts\Http\Headers\SupportedExtensionsInterface;
use Neomerx\JsonApi\Contracts\Schema\ContainerInterface;

/**
 * Diese Factory-Klasse verknüpft die "neomerx/json-api"-Bibliothek mit der
 * Slim-Applikation. Hier wird festgelegt, wie Slim-artige Response-Objekte gebildet
 * werden.
 */
class Responses extends BaseResponses
{
    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var MediaTypeInterface
     */
    private $outputMediaType;

    public function __construct(
        EncoderInterface $encoder,
        MediaTypeInterface $outputMediaType
    ) {
        $this->encoder = $encoder;
        $this->outputMediaType = $outputMediaType;
    }

    /**
     * Diese Methode ist die Schlüsselstelle der ganzen Klasse. Es
     * werden Body, Statuscode und Headers der zukünftigen Response
     * übergeben und eine \Slim\Http\Response zurückgegeben.
     *
     * @param string|null $content    der Body der zukünftigen Response
     * @param int         $statusCode der numerische Statuscode der
     *                                zukünftigen Response
     * @param array       $headers    die Header der zukünftigen Response
     *
     * @return mixed die fertige Slim-Response
     */
    protected function createResponse(?string $content, int $statusCode, array $headers)
    {
        $headers = new Headers($headers);
        $response = new Response($statusCode, $headers);
        $response->getBody()->write($content ?? '');

        return $response->withProtocolVersion('1.1');
    }

    /**
     * {@inheritdoc}
     *
     * @internal
     */
    protected function getEncoder(): EncoderInterface
    {
        return $this->encoder;
    }

    /**
     * {@inheritdoc}
     *
     * @internal
     */
    protected function getMediaType(): MediaTypeInterface
    {
        return $this->outputMediaType;
    }
}
