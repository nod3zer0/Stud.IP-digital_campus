<?php
trait StudipTreeNodeCachableTrait
{
    protected static $descendants_cache_array = null;

    protected static function getDescendantsCacheArray(): StudipCachedArray
    {
        if (self::$descendants_cache_array === null) {
            self::$descendants_cache_array = new StudipCachedArray(
                static::class . '/descendants',
                30 * 60
            );
        }
        return self::$descendants_cache_array;
    }

    protected static function registerCachableCallbacks(array $config): array
    {
        if (!isset($config['registered_callbacks'])) {
            $config['registered_callbacks'] = [];
        }

        if (!isset($config['registered_callbacks']['before_store'])) {
            $config['registered_callbacks']['before_store'] = [];
        }
        $config['registered_callbacks']['before_store'][] = function ($node): void {
            self::getDescendantsCacheArray()->expire();
        };

        if (!isset($config['registered_callbacks']['after_delete'])) {
            $config['registered_callbacks']['after_delete'] = [];
        }
        $config['registered_callbacks']['after_delete'][] = function ($node): void {
            self::getDescendantsCacheArray()->expire();
        };

        return $config;
    }

    protected function getDescendantIds(): array
    {
        $cache = self::getDescendantsCacheArray();

        if (isset($cache[$this->id])) {
            return $cache[$this->id];
        }

        $ids = [];

        foreach ($this->getChildNodes() as $child) {
            $ids = array_merge($ids, [$child->id], $child->getDescendantIds());
        }

        $cache[$this->id] = $ids;

        return $ids;
    }

}
