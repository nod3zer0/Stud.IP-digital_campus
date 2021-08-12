<?php

class MigrationsReloaded extends Migration
{
    public function description()
    {
        return 'add branch column to schema_version';
    }

    public function up()
    {
        $db = DBManager::get();

        $sql = "ALTER TABLE schema_version
                CHANGE domain domain VARCHAR(255) COLLATE latin1_bin NOT NULL,
                ADD branch VARCHAR(64) COLLATE latin1_bin NOT NULL DEFAULT '0' AFTER domain,
                DROP PRIMARY KEY,
                ADD PRIMARY KEY (domain, branch)";
        $db->exec($sql);
    }

    public function down()
    {
        $db = DBManager::get();

        $sql = "DELETE FROM schema_version WHERE branch != '0'";
        $db->exec($sql);

        $sql = 'ALTER TABLE schema_version
                DROP PRIMARY KEY,
                ADD PRIMARY KEY (domain),
                DROP branch';
        $db->exec($sql);
    }
}
