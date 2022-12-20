<?php
/**
 * This class represents an array in cache and removes the neccessity to
 * encode/decode and store the data after every change.
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 * @since   Stud.IP 5.0
 */
class StudipCachedArray implements ArrayAccess
{
    protected $key;
    protected $cache;
    protected $duration;

    protected $data = [];

    protected $hash;

    /**
     * Constructs the cached array
     *
     * @param string $key      Cache key where the array is/should be stored
     *                         an int which will be length of the substring
     *                         of the given chache offset or a callable which
     *                         will return the partition key.
     * @param int    $duration Duration in seconds for which the item shall be
     *                         stored
     */
    public function __construct(string $key, int $duration = StudipCache::DEFAULT_EXPIRATION)
    {
        $this->key = self::class . "/{$key}";
        $this->cache = StudipCacheFactory::getCache();
        $this->duration = $duration;
        $this->hash = $this->getHash();

        $this->reset();
    }

    /**
     * Clears cached values from memory, but does not remove them from the cache.
     */
    public function reset(): void
    {
        $this->data = [];
    }

    /**
     * Removes all values from the cache.
     */
    public function expire(): void
    {
        $this->hash = $this->getHash(true);
        $this->reset();
    }

    /**
     * Determines whether an offset exists in the array.
     *
     * @param string $offset Offset
     *
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        $this->loadData($offset);
        return isset($this->data[$offset]);
    }

    /**
     * Returns the value at given offset or null if it doesn't exist.
     *
     * @param string $offset Offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $this->loadData($offset);
        return $this->data[$offset];
    }

    /**
     * Sets the value for a given offset.
     *
     * @param string $offset Offset
     * @param mixed  $value  Value
     */
    public function offsetSet($offset, $value): void
    {
        if ($offset === null) {
            throw new Exception('Cannot push to cached array, use correct offset instead');
        }

        if (!isset($this->data[$offset]) || $this->data[$offset] !== $value) {
            $this->data[$offset] = $value;

            $this->storeData($offset);
        }
    }

    /**
     * Unsets the value at a given offset
     *
     * @param string $offset Offset
     */
    public function offsetUnset($offset): void
    {
        $this->cache->expire($this->getCacheKey($offset));
        unset($this->data[$offset]);
    }

    /**
     * Loads the data from cache.
     *
     * @param string $offset Offset to load
     */
    protected function loadData(string $offset)
    {
        if (!array_key_exists($offset, $this->data)) {
            $cached = $this->cache->read($this->getCacheKey($offset));
            $this->data[$offset] = $this->swapNullAndFalse($cached);
        }

        return $this->data[$offset];
    }

    /**
     * Stores the data back to the cache.
     * Data needs to be wrapped in another array so that we can correctly read
     * back a value of "false".
     *
     * @param string $offset Offset to store
     */
    protected function storeData(string $offset): void
    {
        $data = $this->swapNullAndFalse($this->data[$offset]);

        $this->cache->write(
            $this->getCacheKey($offset),
            $data,
            $this->duration
        );
    }

    /**
     * Returns the cache key for a specific offset.
     *
     * @param string $offset Offset of the cached item
     *
     * @return string
     */
    private function getCacheKey(string $offset): string
    {
        $key = rtrim($this->key, '/');
        if ($this->hash) {
            $key .= "/{$this->hash}";
        }
        $key .= "/{$offset}";

        return $key;
    }

    /**
     * Swaps null and false for a value because the Stud.IP cache will return
     * false if a cached item is not found instead of null.
     *
     * @param mixed $value Value to swap if appropriate
     *
     * @return mixed
     */
    private function swapNullAndFalse($value)
    {
        if ($value === null) {
            return false;
        }

        if ($value === false) {
            return null;
        }

        return $value;
    }

    /**
     * Loads or creates and stores a hash for this cached array.
     *
     * @return string
     */
    private function getHash(bool $recreate = false): string
    {
        if (!$recreate) {
            $hash = $this->cache->read($this->key);
            return $hash === false ? '' : $hash;
        }

        $hash = md5(uniqid(__CLASS__, true));
        $this->cache->write($this->key, $hash);
        return $hash;
    }
}
