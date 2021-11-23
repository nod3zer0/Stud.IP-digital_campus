<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;
use Neomerx\JsonApi\Schema\Link;

class InstituteMember extends SchemaProvider
{
    const TYPE = 'institute-memberships';
    const REL_INSTITUTE = 'institute';
    const REL_USER = 'user';



    public function getId($membership): ?string
    {
        return $membership->id;
    }

    public function getAttributes($resource, ContextInterface $context): iterable
    {
        $defaultNull = function ($key) use ($resource) {
            return $resource->$key ?: null;
        };

        $attributes = [
            'permission' => $defaultNull('inst_perms'),
            'office-hours' => $defaultNull('sprechzeiten'),
            'location' => $defaultNull('raum'),
            'phone' => $defaultNull('telefon'),
            'fax' => $defaultNull('fax'),
            // 'externdefault' => $defaultNull('externdefault'),
            // 'priority' => $defaultNull('priority'),
            // 'visible' => (bool) $defaultNull('visible'),
        ];

        return $attributes;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRelationships($resource, ContextInterface $context): iterable
    {
        $relationships = [
            self::REL_USER => [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($resource->user),
                ],
                self::RELATIONSHIP_DATA => $resource->user,
            ],

            self::REL_INSTITUTE => [
                self::RELATIONSHIP_LINKS => [
                    Link::RELATED => $this->createLinkToResource($resource->institute),
                ],
                self::RELATIONSHIP_DATA => $resource->institute,
            ],
        ];

        return $relationships;
    }
}
