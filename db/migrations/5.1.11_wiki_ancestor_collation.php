<?php

class WikiAncestorCollation extends Migration
{
    public function description()
    {
        return 'fix collation for ancestor column in wiki';
    }

    public function up()
    {
        $db = DBManager::get();

        $db->exec('ALTER TABLE wiki CHANGE ancestor ancestor VARCHAR(255) COLLATE utf8mb4_bin DEFAULT NULL');
    }
}
