<?php
/**
 * Migrator.php - versioning databases using migrations
 *
 * Migrations can manage the evolution of a schema used by several physical
 * databases. It's a solution to the common problem of adding a field to make a
 * new feature work in your local database, but being unsure of how to push that
 * change to other developers and to the production server. With migrations, you
 * can describe the transformations in self-contained classes that can be
 * checked into version control systems and executed against another database
 * that might be one, two, or five versions behind.
 *
 * General concept
 *
 * Migrations can be described as a triple {sequence of migrations,
 * current schema version, target schema version}. The migrations are "deltas"
 * which are employed to change the schema of your physical database. They even
 * know how to reverse that change. These behaviours are mapped to the methods
 * #up and #down of class Migration. A migration is a subclass of that class and
 * you define the behaviours by overriding methods #up and #down.
 * Broadly spoken the current schema version as well as the target schema
 * version are "pointers" into the sequence of migrations. When migrating the
 * sequence of migrations is traversed between current and target version.
 * If the target version is greater than the current version, the #up methods
 * of the migrations up to the target version's migration are called. If the
 * target version is lower, the #down methods are used.
 *
 * Irreversible transformations
 *
 * Some transformations are destructive in a manner that cannot be reversed.
 * Migrations of that kind should raise an Exception in their #down method.
 *
 * Example of use:
 *
 * Create a directory which will contain your migrations. In this directory
 * create simple php files each containing a single subclass of class Migration.
 * Name this file with the following convention in mind:
 *
 * (\d+)_([a-z_]+).php   // (index)_(name).php
 *
 * 001_my_first_migration.php
 * 002_another_migration.php
 * 003_and_one_last.php
 *
 * Those numbers are used to order your migrations. The first migration has
 * to be a 1 (but you can use leading 0). Every following migration has to be
 * the successor to the previous migration. No gaps are allowed. Just use
 * natural numbers starting with 1.
 *
 * When migrating those numbers are used to determine the migrations needed to
 * fulfill the target version.
 *
 * The current schema version must somehow be persisted using a subclass of
 * SchemaVersion.
 *
 * The name of the migration file is used to deduce the name of the subclass of
 * class Migration contained in the file. Underscores divide the name into words
 * and those words are then concatenated and camelcased.
 *
 * Examples:
 *
 * Name                |   Class
 * ----------------------------------------------------------------------------
 * my_first_migration  |  MyFirstMigration
 * another_migration   |  AnotherMigration
 * and_one_last        |  AndOneLast
 *
 * Those classes have to be subclasses of class Migration.
 *
 * Example:
 *
 * class MyFirstMigration extends Migration {
 *
 *   function description() {
 *     # put your code here
 *     # return migration description
 *   }
 *
 *   function up() {
 *     # put your code here
 *     # create a table for example
 *   }
 *
 *   function down() {
 *     # put your code here
 *     # delete that table
 *   }
 * }
 *
 * After writing your migrations you can invoke the migrator as follows:
 *
 *   $path = '/path/to/my/migrations';
 *
 *   $verbose = TRUE;
 *
 *   # instantiate a schema version persistor
 *   # this one is file based and will use a file in /tmp
 *   $version = new FileSchemaVersion('/tmp');
 *
 *   $migrator = new Migrator($path, $version, $verbose);
 *
 *   # now migrate to target version
 *   $migrator->migrateTo(5);
 *
 * If you want to migrate to the highest migration, you can just use NULL as
 * parameter:
 *
 *   $migrator->migrateTo(null);
 *
 * @author    Marcus Lunzenauer <mlunzena@uos.de>
 * @copyright 2007 Marcus Lunzenauer <mlunzena@uos.de>
 * @license   GPL2 or any later version
 * @package   migrations
 */
class Migrator
{
    /**
     * Direction of migration, either "up" or "down"
     *
     * @var string
     */
    private $direction;

    /**
     * Path to the migration files.
     *
     * @var string
     */
    private $migrations_path;

    /**
     * Specifies the target version, may be NULL (alias for "highest migration")
     *
     * @var array
     */
    private $target_versions;

    /**
     * How verbose shall the migrator be?
     *
     * @var boolean
     */
    private $verbose;

    /**
     * The current schema version persistor.
     *
     * @var SchemaVersion
     */
    private $schema_version;


    /**
     * Constructor.
     *
     * @param string         a file path to the directory containing the migration
     *                       files
     * @param SchemaVersion  the current schema version persistor
     * @param boolean        verbose or not
     *
     * @return void
     */
    public function __construct($migrations_path, SchemaVersion $version, $verbose = false)
    {
        $this->migrations_path = $migrations_path;
        $this->schema_version  = $version;
        $this->verbose         = $verbose;
    }

    /**
     * Sanity check to prevent doublettes.
     *
     * @param array  an array of migration classes
     * @param int    the index of a migration
     */
    private function assertUniqueMigrationVersion($migrations, $version)
    {
        if (isset($migrations[$version])) {
            trigger_error(
                "Multiple migrations have the version number {$version}",
                E_USER_ERROR
            );
        }
    }

    /**
     * Invoking this method will perform the migrations with an index between
     * the current schema version (provided by the SchemaVersion object) and a
     * target version calling the methods #up and #down in sequence.
     *
     * @param mixed  the target version as an integer, array or NULL thus
     *               migrating to the top migrations
     */
    public function migrateTo($target_version)
    {
        $migrations = $this->relevantMigrations($target_version);

        # you're on the right version
        if (empty($migrations)) {
            $this->log('You are already at %d.', $this->schema_version->get());
            return;
        }

        $this->log(
            'Currently at version %d. Now migrating %s to %d.',
            $this->schema_version->get(),
            $this->direction,
            max($this->target_versions)
        );

        foreach ($migrations as $number => $migration) {
            list($branch, $version) = $this->migrationBranchAndVersion($number);

            $action = $this->isUp() ? 'Migrating' : 'Reverting';
            $migration->announce("{$action} %s", $number);

            if ($migration->description()) {
                $this->log($migration->description());
                $this->log(str_repeat('-', 79));
            }

            $time_start = microtime(true);
            $migration->migrate($this->direction);

            $action = $this->isUp() ? 'Migrated' : 'Reverted';
            $this->log('');
            $migration->announce("{$action} in %ss", round(microtime(true) - $time_start, 3));
            $this->log('');

            $this->schema_version->set($this->isDown() ? $version - 1 : $version, $branch);

            $action = $this->isUp() ? 'MIGRATE_UP' : 'MIGRATE_DOWN';
            StudipLog::log($action, $number, $this->schema_version->getDomain());
        }
    }

    /**
     * Calculate the selected target versions for all relevant branches. If a
     * single branch is selected for migration, only that branch and all its
     * children are considered relevant.
     *
     * @param mixed  the target version as an integer, array or NULL thus
     *               migrating to the top migrations
     *
     * @return array an associative array, whose keys are the branch names
     *               and whose values are the target versions
     */
    public function targetVersions($target_version)
    {
        $top_versions = $this->topVersion(true);
        $target_branch = $this->schema_version->getBranch();
  
        if (is_array($target_version)) {
            return $target_version;
        }
  
        $max_version = $target_branch ? $target_branch . '.' . $target_version : $target_version;
  
        foreach ($top_versions as $branch => $version) {
            if ($branch == $target_branch) {
                if (isset($target_version)) {
                    $top_versions[$branch] = $target_version;
                }
            } else if ($target_branch && strpos($branch, $target_branch . '.') !== 0) {
                unset($top_versions[$branch]);
            } else if (isset($target_version) && version_compare($branch, $max_version) >= 0) {
                $top_versions[$branch] = 0;
            }
        }
  
        return $top_versions;
    }
  
    /**
     * Invoking this method will return a list of migrations with an index between
     * the current schema version (provided by the SchemaVersion object) and a
     * target version calling the methods #up and #down in sequence.
     *
     * @param mixed  the target version as an integer, array or NULL thus
     *               migrating to the top migrations
     *
     * @return array an associative array, whose keys are the migration's
     *               version and whose values are the migration objects
     */
    public function relevantMigrations($target_version)
    {
        // Load migrations
        $migrations = $this->migrationClasses();

        // Determine correct target versions
        $this->target_versions = $this->targetVersions($target_version);

        // Determine migration direction
        foreach ($this->target_versions as $branch => $version) {
            if ($this->schema_version->get($branch) < $version) {
                $this->direction = 'up';
                break;
            } else if ($version < $this->schema_version->get($branch)) {
                $this->direction = 'down';
                break;
            }
        }

        // Sort migrations in correct order
        uksort($migrations, 'version_compare');

        if (!$this->isUp()) {
            $migrations = array_reverse($migrations, true);
        }

        $result = [];

        foreach ($migrations as $version => $migration_file_and_class) {
            if (!$this->relevantMigration($version)) {
                continue;
            }

            list($file, $class) = $migration_file_and_class;

            $migration = require_once $file;

            if (!$migration instanceof Migration) {
                $migration = new $class($this->verbose);
            } else {
                $migration->setVerbose($this->verbose);
            }

            $result[$version] = $migration;
        }

        return $result;
    }

    /**
     * Checks wheter a migration has to be invoked, that is if the migration's
     * version is included in the interval between current and target schema
     * version.
     *
     * @param int   the migration's version to check for inclusion
     * @return bool TRUE if included, FALSE otherwise
     */
    private function relevantMigration($version)
    {
        list($branch, $version) = $this->migrationBranchAndVersion($version);
        $current_version = $this->schema_version->get($branch);

        if (!isset($this->target_versions[$branch])) {
            return false;
        } else if ($this->isUp()) {
            return $current_version < $version
                && $version <= $this->target_versions[$branch];
        } else if ($this->isDown()) {
            return $current_version >= $version
                && $version > $this->target_versions[$branch];
        }

        return false;
    }

    /**
     * Am I migrating up?
     *
     * @return bool  TRUE if migrating up, FALSE otherwise
     */
    private function isUp()
    {
        return $this->direction === 'up';
    }

    /**
     * Am I migrating down?
     *
     * @return bool  TRUE if migrating down, FALSE otherwise
     */
    private function isDown()
    {
        return $this->direction === 'down';
    }

    /**
     * Maps a file name to a class name.
     *
     * @param string   part of the file name
     * @return string  the derived class name
     */
    protected function migrationClass($migration)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $migration)));
    }

    /**
     * Returns the collection (an array) of all migrations in this migrator's
     * path.
     *
     * @return array  an associative array, whose keys are the migration's
     *                version and whose values are arrays containing the
     *                migration's file and class name.
     */
    public function migrationClasses()
    {
        $migrations = [];
        foreach ($this->migrationFiles() as $file) {
            list($version, $name) = $this->migrationVersionAndName($file);
            $this->assertUniqueMigrationVersion($migrations, $version);
            $migrations[$version] = [$file, $this->migrationClass($name)];
        }

        return $migrations;
    }

    /**
     * Return all migration file names from my migrations_path.
     *
     * @return array  a collection of file names
     */
    protected function migrationFiles()
    {
        $files = glob($this->migrations_path . '/[0-9]*_*.php');
        return $files;
    }

    /**
     * Split a migration file name into that migration's version and name.
     *
     * @param string  a file name
     * @return array  an array of two elements containing the migration's version
     *                and name.
     */
    protected function migrationVersionAndName($migration_file)
    {
        $matches = [];
        preg_match('/\b([0-9.]+)_([_a-z0-9]*)\.php$/', $migration_file, $matches);
        return [$matches[1], $matches[2]];
    }

    /**
     * Split a migration version into its branch and version parts.
     *
     * @param string  a migration version
     * @return array  an array of two elements containing the migration's branch
     *                and version on this branch.
     */
    public function migrationBranchAndVersion($version)
    {
        if (preg_match('/^(.*)\.([0-9]+)$/', $version, $matches)) {
            $branch = preg_replace('/\b0+/', '', $matches[1]);
            $version = (int) $matches[2];
        } else {
            $branch = '0';
            $version = (int) $version;
        }
        return [$branch, $version];
    }

    /**
     * Returns the top migration's version.
     *
     * @param bool  return top version for all branches, not just default one
     * @return int  the top migration's version.
     */
    public function topVersion($all_branches = false)
    {
        $versions = [0];
        foreach (array_keys($this->migrationClasses()) as $version) {
            list($branch, $version) = $this->migrationBranchAndVersion($version);
            $versions[$branch] = max($versions[$branch], $version);
        }
        return $all_branches ? $versions : $versions[$this->schema_version->getBranch()];
    }

    /**
     * Overridable method used to return a textual representation of what's going
     * on in me. You can use me as you would use printf.
     *
     * @param string $format just a dummy value, instead use this method as you
     *                       would use printf & co.
     */
    protected function log($format)
    {
        if (!$this->verbose) {
            return;
        }

        $args = func_get_args();
        vprintf(array_shift($args) . "\n", $args);
    }
}
