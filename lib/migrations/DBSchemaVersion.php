<?php
/**
 * DBSchemaVersion.php - database backed schema versions
 *
 * Implementation of SchemaVersion interface using a database table.
 *
 * @author    Elmar Ludwig
 * @copyright 2007 Elmar Ludwig
 * @license    GPL2 or any later version
 * @package migrations
 */
class DBSchemaVersion implements SchemaVersion
{
    /**
     * domain name of schema version
     *
     * @var string
     */
    private $domain;

    /**
     * branch of schema version
     *
     * @var string
     */
    private $branch;

    /**
     * current schema version numbers
     *
     * @access private
     * @var array
     */
    private $versions;

    /**
     * Initialize a new DBSchemaVersion for a given domain.
     * The default domain name is 'studip'.
     *
     * @param string $domain domain name (optional)
     * @param string $branch schema branch (optional)
     */
    public function __construct($domain = 'studip', $branch = 0)
    {
        $this->domain = $domain;
        $this->branch = $branch;
        $this->versions = [0];
        $this->initSchemaInfo();
    }

    /**
     * Retrieve the domain name of this schema.
     *
     * @return string domain name
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Retrieve the branch of this schema.
     *
     * @return string schema branch
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * Retrieve all branches of this schema.
     *
     * @return array all schema branches
     */
    public function getAllBranches()
    {
        return array_keys($this->versions);
    }

    /**
     * Check whether the current schema_version supports branches.
     */
    private function branchSupported()
    {
        $result = DBManager::get()->query("DESCRIBE schema_version 'branch'");
        return $result && $result->rowCount() > 0;
    }

    /**
     * Initialize the current schema versions.
     */
    private function initSchemaInfo()
    {
        if (!$this->branchSupported()) {
            $query = "SELECT 0, version FROM schema_version WHERE domain = ?";
        } else {
            $query = "SELECT branch, version FROM schema_version WHERE domain = ? ORDER BY branch";
        }
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->domain]);
        $versions = $statement->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

        if ($versions) {
            $this->versions = array_map('intval', $versions);
        }
    }

    /**
     * Retrieve the current schema version.
     *
     * @param string $branch schema branch (optional)
     * @return int schema version
     */
    public function get($branch = 0)
    {
        return $this->versions[$branch ?: $this->branch];
    }

    /**
     * Set the current schema version.
     *
     * @param int $version new schema version
     * @param string $branch schema branch (optional)
     */
    public function set($version, $branch = 0)
    {
        $this->versions[$branch ?: $this->branch] = (int) $version;

        if (!$this->branchSupported()) {
            $query = "INSERT INTO schema_version (domain, version)
                      VALUES (?, ?)
                      ON DUPLICATE KEY UPDATE version = VALUES(version)";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $this->domain,
                $version
            ]);
        } else {
            $query = "INSERT INTO schema_version (domain, branch, version)
                      VALUES (?, ?, ?)
                      ON DUPLICATE KEY UPDATE version = VALUES(version)";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $this->domain,
                $branch ?: $this->branch,
                $version
            ]);
        }
        NotificationCenter::postNotification(
            'SchemaVersionDidUpdate',
            $this->domain,
            $version
        );
    }

    /**
     * Validate correct structure of schema_version table.
     */
    public static function validateSchemaVersion()
    {
        $db = DBManager::get();
        $result = $db->query("SHOW TABLES LIKE 'schema_versions'");

        if ($result && $result->rowCount() > 0) {
            $backported_migrations = [
                20200306, 20200306, 20200713, 20200811, 20200909,
                20200910, 20201002, 20201103, 202011031, 20210317
            ];

            $query = "DELETE FROM schema_versions
                      WHERE domain = 'studip' AND version in (?)";
            $db->execute($query, [$backported_migrations]);

            $query = "CREATE TABLE schema_version (
                        domain VARCHAR(255) COLLATE latin1_bin NOT NULL,
                        branch VARCHAR(64) COLLATE latin1_bin NOT NULL DEFAULT '0',
                        version INT(11) UNSIGNED NOT NULL,
                        PRIMARY KEY (domain, branch)
                      ) ENGINE=InnoDB ROW_FORMAT=DYNAMIC";
            $db->exec($query);

            $query = "INSERT INTO schema_version
                      SELECT domain, '0', MAX(version) FROM schema_versions
                      GROUP BY domain";
            $db->exec($query);

            $query = "DROP TABLE schema_versions";
            $db->exec($query);
        }
    }
}
