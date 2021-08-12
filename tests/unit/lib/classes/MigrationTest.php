<?php
/**
 * MigrationTest.php - unit tests for the migrations
 *
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 */

class MigrationTest extends \Codeception\Test\Unit
{
    protected $migrator;
    protected $before = null;

    public function setUp(): void
    {
        $this->before = $GLOBALS['CACHING_ENABLE'] ?? null;
        $GLOBALS['CACHING_ENABLE'] = false;

        require_once 'lib/classes/StudipCache.class.php';
        require_once 'lib/classes/StudipMemoryCache.class.php';
        require_once 'lib/classes/StudipCacheFactory.class.php';
        require_once 'lib/models/SimpleORMap.class.php';

        require_once 'lib/migrations/Migration.php';
        require_once 'lib/migrations/Migrator.php';
        require_once 'lib/migrations/SchemaVersion.php';
    }

    public function tearDown(): void
    {
        if ($this->before !== null) {
            $GLOBALS['CACHING_ENABLE'] = $this->before;
        } else {
            unset($GLOBALS['CACHING_ENABLE']);
        }
    }

    private function getSchemaVersion()
    {
        return new class() implements SchemaVersion
        {
            private $versions = [0];

            public function getBranch()
            {
                return 0;
            }

            public function getAllBranches()
            {
                return array_keys($this->versions);
            }

            public function get($branch = 0)
            {
                return $this->versions[$branch];
            }

            public function set($version, $branch = 0)
            {
                $this->versions[$branch] = (int) $version;
            }
        };
    }

    private function getMigrator($schema_version = null)
    {
        return new Migrator(
            TEST_FIXTURES_PATH . 'migrations',
            $schema_version ?: $this->getSchemaVersion()
        );
    }

    public function testRelevance()
    {
        $migrator = $this->getMigrator();

        $relevant = $migrator->relevantMigrations(null);
        $this->assertSame(4, count($relevant));

        $migrator->migrateTo(2);

        $relevant = $migrator->relevantMigrations(null);
        $this->assertSame(1, count($relevant));
    }

    public function testMigrationUp()
    {
        $schema_version = $this->getSchemaVersion();
        $migrator = $this->getMigrator($schema_version);
        $migrator->migrateTo(null);
        $this->assertSame(10, $schema_version->get());
        $this->assertSame(0, count($migrator->relevantMigrations(null)));

        return $schema_version;
    }

    /**
     * @depends testMigrationUp
     */
    public function testMigrationDown($schema_version)
    {
        $migrator = $this->getMigrator($schema_version);
        $migrator->migrateTo(0);
        $this->assertSame(0, $schema_version->get());
        $this->assertSame(4, count($migrator->relevantMigrations(null)));
    }

    public function testGaps()
    {
        $schema_version = $this->getSchemaVersion();
        $schema_version->set(10);

        $migrator = $this->getMigrator($schema_version);

        $relevant = $migrator->relevantMigrations(null);
        $this->assertSame(1, count($relevant));
        $this->assertEquals(['2.1'], array_keys($relevant));
    }
}
