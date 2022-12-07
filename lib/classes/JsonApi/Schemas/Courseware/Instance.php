<?php

namespace JsonApi\Schemas\Courseware;

use JsonApi\Schemas\SchemaProvider;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class Instance extends SchemaProvider
{
    const TYPE = 'courseware-instances';

    const REL_BOOKMARKS = 'bookmarks';
    const REL_ROOT = 'root';

    /**
     * {@inheritdoc}
     */
    public function getId($resource): ?string
    {
        $root = $resource->getRoot();

        return join('_', [$root->range_type, $root->range_id]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($resource, ContextInterface $context): iterable
    {
        $user = $this->currentUser;

        return [
            'block-types' => array_map([$this, 'mapBlockType'], $resource->getBlockTypes()),
            'container-types' => array_map([$this, 'mapContainerType'], $resource->getContainerTypes()),
            'favorite-block-types' => $resource->getFavoriteBlockTypes($user),
            'sequential-progression' => (bool) $resource->getSequentialProgression(),
            'editing-permission-level' => $resource->getEditingPermissionLevel(),
            'certificate-settings' => $resource->getCertificateSettings(),
            'reminder-settings' => $resource->getReminderSettings(),
            'reset-progress-settings' => $resource->getResetProgressSettings()
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function mapBlockType(string $typeClass): array
    {
        return [
            'type' => $typeClass::getType(),
            'title' => $typeClass::getTitle(),
            'description' => $typeClass::getDescription(),
            'categories' => $typeClass::getCategories(),
            'content_types' => $typeClass::getContentTypes(),
            'file_types' => $typeClass::getFileTypes(),
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function mapContainerType(string $typeClass): array
    {
        return [
            'type' => $typeClass::getType(),
            'title' => $typeClass::getTitle(),
            'description' => $typeClass::getDescription(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        $relationships = [];

        $user = $this->currentUser;
        $relationships[self::REL_BOOKMARKS] = [
            self::RELATIONSHIP_LINKS_SELF => true,
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_BOOKMARKS),
            ],
            self::RELATIONSHIP_DATA => $resource->getUsersBookmarks($user),
        ];

        $relationships[self::REL_ROOT] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->createLinkToResource($resource->getRoot()),
            ],
            self::RELATIONSHIP_DATA => $resource->getRoot(),
        ];

        return $relationships;
    }
}
