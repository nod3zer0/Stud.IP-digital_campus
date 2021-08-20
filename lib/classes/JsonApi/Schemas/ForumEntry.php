<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Schema\Link;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use JsonApi\Models\ForumCat;

class ForumEntry extends SchemaProvider
{
    const TYPE = 'forum-entries';
    const REL_CAT = 'category';
    const REL_ENTRY = 'entries';

    public function getId($entry): ?string
    {
        return $entry->topic_id;
    }

    public function getAttributes($entry, ContextInterface $context): iterable
    {
        return [
            'title' => $entry->name,
            'area' => (int) $entry->area,
            'content' => $entry->content,
        ];
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($entry, ContextInterface $context): iterable
    {
        $isPrimary = $context->getPosition()->getLevel() === 0;
        $includeList = $context->getIncludePaths();

        $relationships = [];
        if ($isPrimary) {
            $relationships = $this->addCategoryRelationship($relationships, $entry, $includeList);
            $relationships = $this->addChildEntryRelationship($relationships, $entry, $includeList);
        }

        return $relationships;
    }

    private function addCategoryRelationship($relationships, $entry, $includeList)
    {
        $cat_link = $this->createLinkToResource($entry->category);
        $cat_data = in_array(self::REL_CAT, $includeList)
            ? ForumCat::find($entry->category->id)
            : ForumCat::buildExisting(['id' => $entry->category->id]);

        $relationships[self::REL_CAT] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $cat_link,
            ],
            self::RELATIONSHIP_DATA => $cat_data,
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function addChildEntryRelationship($relationships, $entry, $includeList)
    {
        $relationships[self::REL_ENTRY] = [
            self::RELATIONSHIP_DATA => $entry->getChildEntries($entry->id),

            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($entry, self::REL_ENTRY),
            ],
        ];

        return $relationships;
    }
}
