<?php

namespace JsonApi\Routes\Courseware;

use Courseware\StructuralElement;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Routes\ValidationTrait;
use JsonApi\Schemas\Courseware\StructuralElement as StructuralElementSchema;
use JsonApi\Schemas\FileRef as FileRefSchema;
use JsonApi\Schemas\StockImage as StockImageSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Update one Block.
 */
class StructuralElementsUpdate extends JsonApiController
{
    use EditBlockAwareTrait;
    use ValidationTrait;

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        if (!($resource = StructuralElement::find($args['id']))) {
            throw new RecordNotFoundException();
        }
        $json = $this->validate($request, $resource);
        if (!Authority::canUpdateStructuralElement($user = $this->getUser($request), $resource)) {
            throw new AuthorizationFailedException();
        }
        $resource = $this->updateStructuralElement($user, $resource, $json);

        return $this->getContentResponse($resource);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        if (StructuralElementSchema::TYPE !== self::arrayGet($json, 'data.type')) {
            return 'Wrong `type` member of document´s `data`.';
        }

        if (!self::arrayHas($json, 'data.id')) {
            return 'Document must have an `id`.';
        }

        if (self::arrayHas($json, 'data.relationships.parent')) {
            // Sonderfall: Wurzel hat kein parent und kann auch nicht verändert werden
            if ($data->isRootNode()) {
                if (null !== self::arrayGet($json, 'data.relationships.parent.data')) {
                    return 'Cannot modify `parent` of a root node.';
                }

                // Regelfall: Es gibt die Relation, aber `parent` ist ungültig.
            } else {
                $parent = $this->getParentFromJson($json);
                if (!$parent) {
                    return 'Invalid `parent` relationship.';
                }

                // keine Schleifen
                if (
                    in_array(
                        $data->id,
                        array_merge(
                            [$parent->id],
                            array_map(function ($ancestor) {
                                return $ancestor->id;
                            }, $parent->findAncestors())
                        )
                    )
                ) {
                    return 'Invalid `parent` relationship resulting in a cycle.';
                }
            }
        }

        $imageRelationship = 'data.relationships.' . StructuralElementSchema::REL_IMAGE;
        if (self::arrayHas($json, $imageRelationship)) {
            $relation = self::arrayGet($json, $imageRelationship);
            if (isset($relation['data']['type'])) {
                $validTypes = [FileRefSchema::TYPE, StockImageSchema::TYPE];
                if (!in_array($relation['data']['type'], $validTypes)) {
                    return 'Relationship `image` can only be of type ' . join(', ', $validTypes);
                }
            }
        }
    }

    private function getParentFromJson($json)
    {
        if (!$this->validateResourceObject($json, 'data.relationships.parent', StructuralElementSchema::TYPE)) {
            return null;
        }
        $parentId = self::arrayGet($json, 'data.relationships.parent.data.id');

        return \Courseware\StructuralElement::find($parentId);
    }

    private function updateStructuralElement(\User $user, StructuralElement $resource, array $json): StructuralElement
    {
        return $this->updateLockedResource($user, $resource, function ($user, $resource) use ($json) {
            $attributes = [
                'copy-approval',
                'external-relations',
                'payload',
                'position',
                'public',
                'purpose',
                'read-approval',
                'release-date',
                'title',
                'withdraw-date',
                'write-approval',
            ];

            foreach ($attributes as $jsonKey) {
                $sormKey = strtr($jsonKey, '-', '_');
                if ($val = self::arrayGet($json, 'data.attributes.' . $jsonKey, '')) {
                    $resource->$sormKey = $val;
                }
            }

            if (isset($json['data']['attributes']['release-date'])) {
                $resource->release_date = $json['data']['attributes']['release-date'];
            }

            if (isset($json['data']['attributes']['withdraw-date'])) {
                $resource->withdraw_date = $json['data']['attributes']['withdraw-date'];
            }

            if (isset($json['data']['attributes']['commentable'])) {
                $resource->commentable = $json['data']['attributes']['commentable'];
            }

            // update parent
            if (self::arrayHas($json, 'data.relationships.parent')) {
                $parent = $this->getParentFromJson($json);
                $resource->parent_id = $parent->id;
            }

            // update image
            $this->updateImage($resource, $json);

            $resource->editor_id = $user->id;
            $resource->store();

            return $resource;
        });
    }

    private function updateImage(StructuralElement $resource, array $json): void
    {
        if (!$this->imageNeedsUpdate($resource, $json)) {
            return;
        }

        $currentImage = $resource->image;
        list($imageType, $imageId) = $this->getImageRelationshipData($json);

        // remove current image
        if (!$imageType && !$imageId) {
            if (is_a($currentImage, \FileRef::class)) {
                $currentImage->getFileType()->delete();
            }
            $resource->image_id = null;
            $resource->image_type = null;
        } elseif ($imageType === StockImageSchema::TYPE) {
            $stockImageExists = \StockImage::countBySQL('id = ?', [$imageId]);
            if (!$stockImageExists) {
                throw new RecordNotFoundException('Could not find that stock image.');
            }
            $resource->image_id = $imageId;
            $resource->image_type = \StockImage::class;
        } elseif ($imageType === FileRefSchema::TYPE) {
            throw new \RuntimeException('Not yet implemented.');
        }
    }

    private function getImageRelationshipData(array $json): array
    {
        $imageRelationship = 'data.relationships.' . StructuralElementSchema::REL_IMAGE;
        if (!self::arrayHas($json, $imageRelationship)) {
            throw new \RuntimeException('Missing relationship `image`');
        }
        $relation = self::arrayGet($json, $imageRelationship);

        return [self::arrayGet($relation, 'data.type'), self::arrayGet($relation, 'data.id')];
    }

    private function imageNeedsUpdate(StructuralElement $resource, array $json): bool
    {
        $imageRelationship = 'data.relationships.' . StructuralElementSchema::REL_IMAGE;
        if (!self::arrayHas($json, $imageRelationship)) {
            return false;
        }

        $currentImage = $resource->image;
        list($imageType, $imageId) = $this->getImageRelationshipData($json);

        if (!$currentImage) {
            return (bool) $imageId;
        }

        $currentImageSchema = $this->getSchema($currentImage);

        return ($currentImage && !$imageId)
            || $currentImageSchema::TYPE !== $imageType
            || $currentImage->id != $imageId;
    }
}
