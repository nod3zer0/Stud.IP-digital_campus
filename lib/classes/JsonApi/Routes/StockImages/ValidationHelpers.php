<?php

namespace JsonApi\Routes\StockImages;

trait ValidationHelpers
{
    protected static function getAttributeUpdates($json, iterable $keys): iterable
    {
        return array_reduce(
            $keys,
            function ($memo, $key) use ($json) {
                $path = 'data.attributes.' . $key;
                if (self::arrayHas($json, $path)) {
                    $memo[$key] = self::arrayGet($json, $path);
                }
                return $memo;
            },
            []
        );
    }

    protected static function getTags($json): iterable
    {
        return ['tags' => json_encode(self::arrayGet($json, 'data.attributes.tags', []))];
    }
}
