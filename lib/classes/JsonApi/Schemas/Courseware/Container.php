<?php

namespace JsonApi\Schemas\Courseware;

use JsonApi\Schemas\SchemaProvider;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class Container extends SchemaProvider
{
    const TYPE = 'courseware-containers';

    const REL_BLOCKS = 'blocks';
    const REL_OWNER = 'owner';
    const REL_EDITOR = 'editor';
    const REL_EDITBLOCKER = 'edit-blocker';
    const REL_STRUCTURAL_ELEMENT = 'structural-element';

    /**
     * {@inheritdoc}
     */
    public function getId($resource): ?string
    {
        return $resource->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($resource, ContextInterface $context): iterable
    {
        return [
            'position' => (int) $resource['position'],
            'site' => (int) $resource['site'],
            'container-type' => (string) $resource['container_type'],
            'title' => (string) $resource->type->getTitle(),
            'width' => (string) $resource->type->getContainerWidth(),
            'visible' => (bool) $resource['visible'],
            'payload' => $resource['payload']->getIterator(),
            'mkdate' => date('c', $resource['mkdate']),
            'chdate' => date('c', $resource['chdate']),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        $isPrimary = $context->getPosition()->getLevel() === 0;
        $includeList = $context->getIncludePaths();

        $relationships = [];

        $shouldInclude = function ($key) use ($includeList) {
            return in_array($key, $includeList);
        };

        $relationships = $this->addBlocksRelationship($relationships, $resource, $shouldInclude(self::REL_BLOCKS));

        $relationships[self::REL_OWNER] = $resource['owner_id']
            ? [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($resource->owner),
                ],
                self::RELATIONSHIP_DATA => $resource->owner,
            ]
            : [self::RELATIONSHIP_DATA => $resource->owner];

        $relationships[self::REL_EDITOR] = $resource['editor_id']
            ? [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($resource->editor),
                ],
                self::RELATIONSHIP_DATA => $resource->editor,
            ]
            : [self::RELATIONSHIP_DATA => null];

        $relationships[self::REL_EDITBLOCKER] = $resource['edit_blocker_id']
            ? [
                self::RELATIONSHIP_LINKS_SELF => true,
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($resource->edit_blocker),
                ],
                self::RELATIONSHIP_DATA => $resource->edit_blocker,
            ]
            : [self::RELATIONSHIP_LINKS_SELF => true, self::RELATIONSHIP_DATA => null];

        $relationships[self::REL_STRUCTURAL_ELEMENT] = $resource['structural_element_id']
            ? [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($resource->structural_element),
                ],
                self::RELATIONSHIP_DATA => $resource->structural_element,
            ]
            : [self::RELATIONSHIP_DATA => null];

        return $relationships;
    }

    private function addBlocksRelationship(array $relationships, $resource, $includeData)
    {
        $relation = [
            self::RELATIONSHIP_LINKS_SELF => true,
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_BLOCKS),
            ],
        ];

        $relation[self::RELATIONSHIP_DATA] = $resource->blocks;

        $relationships[self::REL_BLOCKS] = $relation;

        return $relationships;
    }
}
