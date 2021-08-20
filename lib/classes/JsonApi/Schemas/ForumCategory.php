<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;
use JsonApi\Models\ForumEntry as Entry;

class ForumCategory extends SchemaProvider
{
    const TYPE = 'forum-categories';
    const REL_COURSE = 'course';
    const REL_ENTRY = 'entries';



    public function getId($category): ?string
    {
        return $category->id;
    }

    public function getAttributes($category, ContextInterface $context): iterable
    {
        return [
            'title' => $category->entry_name,
            'position' => (int) $category->pos,
        ];
    }

    public function getRelationships($category, ContextInterface $context): iterable
    {
        $isPrimary = $context->getPosition()->getLevel() === 0;
        $includeList = $context->getIncludePaths();

        $relationships = [];
        if ($isPrimary) {
            $relationships = $this->addCourseRelationship($category, $isPrimary, $includeList);
            $relationships = $this->addEntryRelationship($category, $isPrimary, $includeList);
        }

        return $relationships;
    }

    public function addCourseRelationship($category, $isPrimary, $includeList)
    {
        $data = $isPrimary && in_array(self::REL_COURSE, $includeList)
              ? \Course::find($category->seminar_id)
              : \Course::buildExisting(['id' => $category->seminar_id]);
        $link = $this->createLinkToResource($data);
        $relationships = [
            self::REL_COURSE => [
                self::RELATIONSHIP_LINKS => [Link::RELATED => $link],
                self::RELATIONSHIP_DATA => $data,
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function addEntryRelationship($category, $isPrimary, $includeList)
    {
        $data = Entry::getEntriesFromCat($category);
        $link = $this->getRelationshipRelatedLink($category, self::REL_ENTRY);
        $relationships[self::REL_ENTRY] = [
            self::RELATIONSHIP_DATA => $data,
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $link,
            ],
        ];

        return $relationships;
    }
}
