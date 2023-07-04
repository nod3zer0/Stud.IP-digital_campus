<?php

namespace JsonApi\Routes\StockImages;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\StockImage as ResourceSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use StockImage;
use User;

/**
 * Creates a stock image.
 */
class StockImagesCreate extends JsonApiController
{
    use ValidationTrait;
    use ValidationHelpers;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $json = $this->validate($request, $resource);
        $user = $this->getUser($request);
        if (!Authority::canCreateStockImage($user, $resource)) {
            throw new AuthorizationFailedException();
        }
        $resource = $this->createResource($json);

        return $this->getContentResponse($resource);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        if (ResourceSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }

        $errors = iterator_to_array(self::requiredAttributes($json, ['title', 'description', 'author', 'license']));
        if (!empty($errors)) {
            return current($errors);
        }

        // optional attribute `tags` has to be an array
        if (self::arrayHas($json, 'data.attributes.tags')) {
            $tags = self::arrayGet($json, 'data.attributes.tags', []);
            if (!is_array($tags) || !array_is_list($tags)) {
                return 'Attribute `tags` has to be a list of strings.';
            }
        }
    }

    protected static function requiredAttributes($json, $keys)
    {
        foreach ($keys as $key) {
            $path = 'data.attributes.' . $key;
            $value = self::arrayGet($json, $path, '');
            if (empty($value)) {
                yield sprintf('Missing or empty attribute `%s`', $key);
            }
        }
    }

    private function createResource(array $json): StockImage
    {
        $resource = new StockImage();
        $resource->setData(
            array_merge(
                self::getAttributeDefaults(),
                self::getAttributeUpdates($json, ['title', 'description', 'author', 'license']),
                self::getTags($json)
            )
        );
        $resource->store();

        return $resource;
    }

    private static function getAttributeDefaults(): iterable
    {
        return [
            'height' => 0,
            'mime_type' => '',
            'size' => 0,
            'width' => 0,
            'tags' => '[]',
        ];
    }
}
