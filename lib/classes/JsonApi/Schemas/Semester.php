<?php

namespace JsonApi\Schemas;

use Neomerx\JsonApi\Contracts\Schema\ContextInterface;

class Semester extends SchemaProvider
{
    const TYPE = 'semesters';

    public function getId($semester): ?string
    {
        return $semester->id;
    }

    public function getAttributes($semester, ContextInterface $context): iterable
    {
        return [
            'title' => (string) $semester->name,
            'token' => (string) $semester->token,
            'start' => date('c', $semester->beginn),
            'end' => date('c', $semester->ende),
            'start-of-lectures' => date('c', $semester->vorles_beginn),
            'end-of-lectures' => date('c', $semester->vorles_ende),
            'visible' => (bool) $semester->visible,
            'is-current' => $semester->isCurrent(),
        ];
    }

    public function getRelationships($user, ContextInterface $context): iterable
    {
        return [];
    }
}
