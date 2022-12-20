<?php
/**
 * StudipCachedArrayTest.php - unit tests for the StudipCachedArray class
 *
 * @author   Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license  GPL2 or any later version
 *
 * @covers StudipCachedArray
 * @uses StudipMemoryCache
 */

class StudipCachedArrayTest extends \Codeception\Test\Unit
{
    private function getCachedArray()
    {
        return new StudipCachedArray(__CLASS__);
    }

    /**
     * @dataProvider StorageProvider
     */
    public function testStorage($key, $value)
    {
        $cache = $this->getCachedArray();

        // Cache should be empty
        $this->assertFalse(isset($cache[$key]));

        // Set value
        $cache[$key] = $value;

        // Immediate response
        $this->assertTrue(isset($cache[$key]));
        $this->assertEquals($value, $cache[$key]);

        // When reading back from cache
        $cache->reset();

        $this->assertTrue(isset($cache[$key]));
        $this->assertEquals($value, $cache[$key]);

        // Remove value
        unset($cache[$key]);
        $this->assertFalse(isset($cache[$key]));

        $cache->reset();

        $this->assertFalse(isset($cache[$key]));
    }

    /**
     * @depends testStorage
     * @dataProvider StorageProvider
     */
    public function testExpiration($key, $value)
    {
        $cache = $this->getCachedArray();

        $cache[$key] = $value;

        $cache->expire();

        $this->assertFalse(isset($cache[$key]));
    }

    public function StorageProvider(): array
    {
        return [
            // 'null'   => [1, null], // Null is not really testable
            'true'   => [2, true],
            'false'  => [3, false],
            'int'    => [4, 42],
            'string' => ['string', 'bar'],
            'array'  => ['array', ['foo']],
            'object' => ['object', new StudipCachedArrayTestClass()],
        ];
    }
}

// Simple test class
class StudipCachedArrayTestClass
{
    private $foo = 42;
}
