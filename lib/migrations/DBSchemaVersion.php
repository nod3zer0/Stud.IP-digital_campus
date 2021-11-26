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
            $base_version = 269;    // 4.4
            $schema_mapping = [
                20200307 => 285,    // 4.5
                20200522 => 290,    // 4.6
                20210511 => 327     // 5.0
            ];

            foreach ($schema_mapping as $old_version => $new_version) {
                $query = "SELECT 1 FROM schema_versions
                          WHERE domain = 'studip' AND version = ?";
                $result = $db->fetchOne($query, [$old_version]);

                if ($result) {
                    $base_version = $new_version;
                }
            }

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

            $query = "UPDATE schema_version SET branch = ?, version = ?
                      WHERE domain = 'studip'";
            $db->execute($query, [1, $base_version]);
        }
    }
}
