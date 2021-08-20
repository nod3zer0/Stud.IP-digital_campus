<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class WikiPage extends SchemaProvider
{
    const REGEXP_KEYWORD = '/([\w\.\-\:\(\)_§\/@# ]|&[AOUaou]uml;|&szlig;)+/A';

    const TYPE = 'wiki-pages';
    const REL_AUTHOR = 'author';
    const REL_CHILDREN = 'children';
    const REL_DESCENDANTS = 'descendants';
    const REL_PARENT = 'parent';
    const REL_RANGE = 'range';

    public static function getRangeClasses(): array
    {
        return [
            'sem' => \Course::class,
            'inst' => \Institute::class,
        ];
    }

    public static function getRangeTypes(): array
    {
        return [
            'sem' => Course::TYPE,
            'inst' => Institute::TYPE,
        ];
    }

    /**
     * @param mixed $resource
     *
     * @return ?string
     */
    public static function getRangeClass($resource)
    {
        $classes = self::getRangeClasses();

        return $classes[
            get_object_type(
                $resource->range_id,
                array_keys($classes)
            )
        ];
    }

    /**
     * @param mixed $resource
     *
     * @return ?string
     */
    public static function getRangeType($resource)
    {
        $types = self::getRangeTypes();

        return $types[
            get_object_type(
                $resource->range_id,
                array_keys($types)
            )
        ];
    }

    public function getId($wiki): ?string
    {
        return sprintf(
            '%s_%s',
            $wiki->range_id,
            $wiki->keyword
        );
    }

    public function getAttributes($wiki, ContextInterface $context): iterable
    {
        return [
            'keyword' => $wiki->keyword,
            'content' => $wiki->body,
            'chdate' => date('c', $wiki->chdate),
            'version' => (int) $wiki->version,
        ];
    }

    public function getRelationships($wiki, ContextInterface $context): iterable
    {
        $isPrimary = $context->getPosition()->getLevel() === 0;
        $includeList = $context->getIncludePaths();

        $relationships = [];

        if ($isPrimary) {
            $relationships = $this->addAuthorRelationship($relationships, $wiki, $includeList);
            $relationships = $this->addChildrenRelationship($relationships, $wiki, $includeList);
            $relationships = $this->addDescendantsRelationship($relationships, $wiki, $includeList);
            $relationships = $this->addParentRelationship($relationships, $wiki, $includeList);
            $relationships = $this->addRangeRelationship($relationships, $wiki, $includeList);
        }

        return $relationships;
    }

    private function addParentRelationship(array $relationships, \WikiPage $wiki, array $includeList): array
    {
        $related = $wiki->parent;
        $relationships[self::REL_PARENT] = [
            self::RELATIONSHIP_LINKS_SELF => true,
            self::RELATIONSHIP_DATA => $related
        ];
        if ($related) {
            $relationships[self::REL_PARENT][self::RELATIONSHIP_LINKS] = [
                Link::RELATED => $this->createLinkToResource($related),
            ];
        }

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function addChildrenRelationship(array $relationships, \WikiPage $wiki, array $includeList): array
    {
        $relationships[self::REL_CHILDREN] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($wiki, self::REL_CHILDREN),
            ],
        ];

        return $relationships;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function addDescendantsRelationship(array $relationships, \WikiPage $wiki, array $includeList): array
    {
        $relationships[self::REL_DESCENDANTS] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($wiki, self::REL_DESCENDANTS),
            ],
        ];

        return $relationships;
    }

    /**
     * @param array $relationships
     * @param \WikiPage $wiki
     * @param array $includeList
     *
     * @return array
     */
    private function addAuthorRelationship($relationships, $wiki, $includeList)
    {
        if ($wiki->author) {
            $relationships[self::REL_AUTHOR] = [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($wiki->author),
                ],
                self::RELATIONSHIP_DATA => $wiki->author,
            ];
        }

        return $relationships;
    }

    /**
     * @param array $relationships
     * @param \WikiPage $wiki
     * @param array $includeList
     *
     * @return array
     */
    private function addRangeRelationship($relationships, $wiki, $includeList)
    {
        $range = $this->prepareRange($wiki);
        $relationships[self::REL_RANGE] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->createLinkToResource($range),
            ],
            self::RELATIONSHIP_DATA => $range,
        ];

        return $relationships;
    }

    private function prepareRange(\WikiPage $wiki): \Range
    {
        $class = self::getRangeClass($wiki);

        /** @var \Range $range */
        $range = $class::build(['id' => $wiki->range_id], false);

        return  $range;
    }
}
