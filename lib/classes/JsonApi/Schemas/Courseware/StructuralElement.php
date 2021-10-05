<?php

namespace JsonApi\Schemas\Courseware;

use JsonApi\Schemas\SchemaProvider;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class StructuralElement extends SchemaProvider
{
    const TYPE = 'courseware-structural-elements';

    const REL_ANCESTORS = 'ancestors';
    const REL_CHILDREN = 'children';
    const REL_CONTAINERS = 'containers';
    const REL_COURSE = 'course';
    const REL_DESCENDANTS = 'descendants';
    const REL_EDITBLOCKER = 'edit-blocker';
    const REL_EDITOR = 'editor';
    const REL_IMAGE = 'image';
    const REL_OWNER = 'owner';
    const REL_PARENT = 'parent';
    const REL_USER = 'user';

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
        $user = $this->currentUser;

        return [
            'position' => (int) $resource['position'],
            'title' => (string) $resource['title'],
            'purpose' => (string) $resource['purpose'],
            'payload' => $resource['payload']->getIterator(),
            'public' => (int) $resource['public'],
            'release-date' => $resource['release_date'] ? date('Y-m-d', (int) $resource['release_date']) : null,
            'withdraw-date' => $resource['withdraw_date'] ? date('Y-m-d', (int) $resource['withdraw_date']) : null,
            'read-approval' => $resource['read_approval']->getIterator(),
            'write-approval' => $resource['write_approval']->getIterator(),
            'copy-approval' => $resource['copy_approval']->getIterator(),
            'can-edit' => $resource->canEdit($user),
            'can-read' => $resource->canRead($user),

            'external-relations' => $resource['external_relations']->getIterator(),
            'mkdate' => date('c', $resource['mkdate']),
            'chdate' => date('c', $resource['chdate']),
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @param StructuralElement $resource
     * @param ContextInterface $context
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        $relationships = [];

        $relationships[self::REL_CHILDREN] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_CHILDREN),
            ],
            self::RELATIONSHIP_DATA => $resource->children,
        ];

        $relationships[self::REL_CONTAINERS] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_CONTAINERS),
            ],
            self::RELATIONSHIP_DATA => $resource->containers,
        ];

        if ($resource->course) {
            $relationships[self::REL_COURSE] = [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($resource->course),
                ],
                self::RELATIONSHIP_DATA => $resource->course,
            ];
        }

        if ($resource->user) {
            $relationships[self::REL_USER] = [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($resource->user),
                ],
                self::RELATIONSHIP_DATA => $resource->user,
            ];
        }

        $relationships[self::REL_OWNER] = $resource['owner_id']
            ? [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($resource->owner),
                ],
                self::RELATIONSHIP_DATA => $resource->owner,
            ]
            : [self::RELATIONSHIP_DATA => null];

        $relationships[self::REL_EDITOR] = $resource['editor_id']
            ? [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($resource->editor),
                ],
                self::RELATIONSHIP_DATA => $resource->editor,
            ]
            : [self::RELATIONSHIP_DATA => $resource->editor];

        $relationships[self::REL_EDITBLOCKER] = $resource['edit_blocker_id']
            ? [
                self::RELATIONSHIP_LINKS_SELF => true,
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($resource->edit_blocker),
                ],
                self::RELATIONSHIP_DATA => $resource->edit_blocker,
            ]
            : [self::RELATIONSHIP_LINKS_SELF => true, self::RELATIONSHIP_DATA => null];

        $relationships[self::REL_PARENT] = $resource->parent_id
            ? [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($resource->parent),
                ],

                self::RELATIONSHIP_DATA => $resource->parent,
            ]
            : [self::RELATIONSHIP_DATA => null];

        $relationships = $this->addAncestorsRelationship(
            $relationships,
            $resource,
            $this->shouldInclude($context, self::REL_ANCESTORS)
        );

        $relationships = $this->addDescendantsRelationship(
            $relationships,
            $resource,
            $this->shouldInclude($context, self::REL_DESCENDANTS)
        );

        $relationships = $this->addImageRelationship(
            $relationships,
            $resource,
            $this->shouldInclude($context, self::REL_IMAGE)
        );

        return $relationships;
    }

    private function addAncestorsRelationship(array $relationships, $resource, $includeData)
    {
        $relation = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_ANCESTORS),
            ],
        ];

        if ($includeData) {
            $related = $resource->findAncestors();
            $relation[self::RELATIONSHIP_DATA] = $related;
        }

        $relationships[self::REL_ANCESTORS] = $relation;

        return $relationships;
    }

    private function addDescendantsRelationship(array $relationships, $resource, $includeData)
    {
        $relation = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_DESCENDANTS),
            ],
        ];

        if ($includeData) {
            $related = $resource->findDescendants();
            $relation[self::RELATIONSHIP_DATA] = $related;
        }

        $relationships[self::REL_DESCENDANTS] = $relation;

        return $relationships;
    }

    private function addImageRelationship(array $relationships, $resource, $includeData)
    {
        $relation = [
            self::RELATIONSHIP_DATA => $resource->image ?: null,
        ];

        if ($resource->image) {
            $relation[self::RELATIONSHIP_META] = [
                'download-url' => $resource->image->getFileType()->getDownloadURL(),
            ];
        }

        $relationships[self::REL_IMAGE] = $relation;

        return $relationships;
    }
}
