<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Schema\Link;
use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

class BlubberComment extends SchemaProvider
{
    const TYPE = 'blubber-comments';
    const REL_AUTHOR = 'author';
    const REL_MENTIONS = 'mentions';
    const REL_THREAD = 'thread';

    public function getId($resource): ?string
    {
        return $resource->id;
    }

    public function getAttributes($resource, ContextInterface $context): iterable
    {
        $attributes = [
            # `network` VARCHAR(64) COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
            'content' => $resource['content'],
            'content-html' => blubberReady($resource['content']),

            'mkdate' => date('c', $resource['mkdate']),
            'chdate' => date('c', $resource['chdate']),
        ];

        return $attributes;
    }

    /**
     * In dieser Methode können Relationships zu anderen Objekten
     * spezifiziert werden.
     * {@inheritdoc}
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        $isPrimary = $context->getPosition()->getLevel() === 0;
        $includeList = $context->getIncludePaths();

        $shouldInclude = function ($key) use ($isPrimary, $includeList) {
            return $isPrimary && in_array($key, $includeList);
        };

        $relationships = [];
        $relationships = $this->getAuthorRelationship($relationships, $resource, $shouldInclude(self::REL_AUTHOR));

        if (!$isPrimary) {
            return $relationships;
        }

        $relationships = $this->getMentionsRelationship($relationships, $resource, $shouldInclude(self::REL_MENTIONS));
        $relationships = $this->getThreadRelationship($relationships, $resource, $shouldInclude(self::REL_THREAD));

        return $relationships;
    }

    // #### PRIVATE HELPERS ####

    private function getAuthorRelationship($relationships, $resource, $includeData)
    {
        if (!$resource['external_contact']) {
            $userId = $resource['user_id'];
            $data = $includeData ? \User::find($userId) : \User::build(['id' => $userId], false);
            $relationships[self::REL_AUTHOR] = [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($data),
                ],
                self::RELATIONSHIP_DATA => $data,
            ];
        }

        return $relationships;
    }

    private function getMentionsRelationship(array $relationships, \BlubberComment $resource, $includeData)
    {
        if ($includeData) {
            $relatedUsers = $resource->mentions->pluck('user');
        } else {
            $relatedUsers = array_map(function ($mention) {
                return \User::build(['user_id' => $mention->user_id], false);
            }, \BlubberMention::findBySQL('thread_id = ?', [$resource->id]));
        }

        $relationships[self::REL_MENTIONS] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->getRelationshipRelatedLink($resource, self::REL_MENTIONS),
            ],
            self::RELATIONSHIP_DATA => $relatedUsers,
        ];

        return $relationships;
    }

    private function getThreadRelationship(array $relationships, \BlubberComment $resource, $includeData)
    {
        if ($includeData) {
            $related = $resource->thread;
        } else {
            $related = \BlubberThread::build(['id' => $resource->thread_id], false);
        }

        $relationships[self::REL_THREAD] = [
            self::RELATIONSHIP_LINKS => [
                Link::RELATED => $this->createLinkToResource($related),
            ],
            self::RELATIONSHIP_DATA => $related,
        ];

        return $relationships;
    }
}
