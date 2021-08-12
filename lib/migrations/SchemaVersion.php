<?php
/**
 * SchemaVersion.php - schema version interface for migrations
 *
 * This interface provides an abstract way to retrieve and set the current
 * version of a schema. Implementations of this interface need to define
 * where the version information is actually stored (e.g. in a file).
 *
 * @author    Marcus Lunzenauer <mlunzena@uos.de>
 * @copyright 2007 - Marcus Lunzenauer <mlunzena@uos.de>
 * @license   GPL2 or any later version
 * @package   migrations
 */
interface SchemaVersion
{
    /**
     * Retrieve the branch of this schema.
     *
     * @return string schema branch
     */
    public function getBranch();

    /**
     * Retrieve all branches of this schema.
     *
     * @return array all schema branches
     */
    public function getAllBranches();

    /**
     * Returns current schema version.
     *
     * @param string $branch schema branch (optional)
     * @return int schema version
     */
    public function get($branch = 0);

    /**
     * Sets the new schema version.
     *
     * @param int $version new schema version
     * @param string $branch schema branch (optional)
     */
    public function set($version, $branch = 0);
}
