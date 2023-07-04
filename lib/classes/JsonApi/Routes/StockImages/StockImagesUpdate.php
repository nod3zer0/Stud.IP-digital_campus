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
 * Updates a stock image.
 */
class StockImagesUpdate extends JsonApiController
{
    use ValidationTrait;
    use ValidationHelpers;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $resource = StockImage::find($args['id']);
        if (!$resource) {
            throw new RecordNotFoundException();
        }

        $json = $this->validate($request, $resource);
        $user = $this->getUser($request);
        if (!Authority::canUpdateStockImage($user, $resource)) {
            throw new AuthorizationFailedException();
        }
        $resource = $this->updateResource($resource, $json);

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

        if (!self::arrayHas($json, 'data.id')) {
            return 'Document must have an `id`.';
        }

        $errors = iterator_to_array(self::nonEmptyAttributes($json, ['title', 'description', 'author', 'license']));
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

    protected static function nonEmptyAttributes($json, $keys)
    {
        foreach ($keys as $key) {
            $path = 'data.attributes.' . $key;
            if (self::arrayHas($json, $path)) {
                $value = self::arrayGet($json, $path);
                if (empty($value)) {
                    yield sprintf('Attribute `%s` must not be empty', $key);
                }
            }
        }
    }

    private function updateResource(StockImage $resource, array $json): void
    {
        $updates = array_merge(
            self::getAttributeUpdates($json, ['title', 'description', 'author', 'license']),
            self::getTags($json)
        );
        $resource->setData($updates);
        $resource->store();
    }
}
