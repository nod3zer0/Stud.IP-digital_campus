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
        $this->validateSchemaVersion();
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
            $branch = $this->domain === 'studip' ? 1 : 0;
            $query = "SELECT $branch, version FROM schema_version WHERE domain = ?";
        } else {
            $query = "SELECT branch, version FROM schema_version WHERE domain = ? ORDER BY branch";
        }
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->domain]);
        $versions = $statement->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

        foreach ($versions as $branch => $version) {
            $this->versions[$branch] = (int) $version;
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
        return $this->versions[$branch];
    }

    /**
     * Set the current schema version.
     *
     * @param int $version new schema version
     * @param string $branch schema branch (optional)
     */
    public function set($version, $branch = 0)
    {
        $this->versions[$branch] = (int) $version;

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
                $branch,
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
     * Validate correct structure of schema_version table. This
     * will upgrade the schema from 4.4 style to 5.1 if necessary.
     */
    private function validateSchemaVersion()
    {
        $db = DBManager::get();
        $result = $db->query("SHOW TABLES LIKE 'schema_versions'");

        if ($result && $result->rowCount() > 0) {
            $backported_migrations = [
                20200306, 20200713, 20200811, 20200909, 20200910,
                20201002, 20201103, 202011031, 20210317, 20210422,
                20210425, 20210503, 20211015, 20211108,
            ];

            // drop backported migrations
            $query = "DELETE FROM schema_versions
                      WHERE domain = 'studip' AND version in (?)";
            $db->execute($query, [$backported_migrations]);

            // drop migrations with irregular numbers
            $query = "DELETE FROM schema_versions
                      WHERE domain = 'studip' AND LENGTH(version) > 8";
            $db->exec($query);

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

            $schema_mapping = [
                20190917 => 269,
                20200307 => 285,
                20200522 => 290,
                20210511 => 327,
                20210603 => 327
            ];

            $query = "UPDATE schema_version SET branch = '1' WHERE domain = 'studip'";
            $db->exec($query);

            foreach ($schema_mapping as $old_version => $new_version) {
                $query = "UPDATE schema_version SET version = ?
                          WHERE domain = 'studip' AND version = ?";
                $db->execute($query, [$new_version, $old_version]);
            }
        }
    }
}
