<?php
/**
 * @see https://gitlab.studip.de/studip/studip/-/issues/881
 */
final class RemoveColumnTermineTopicId extends Migration
{
    public function description()
    {
        return 'Removes unused column topic_id from table termine.';
    }

    protected function up()
    {
        if (!$this->columnExists('termine', 'topic_id')) {
            $this->write("Column termine.topic_id does not exist");
            return;
        }

        $query = "ALTER TABLE `termine`
                  DROP COLUMN `topic_id`";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        if ($this->columnExists('termine', 'topic_id')) {
            $this->write("Column termine.topic_id already exists");
            return;
        }

        $query = "ALTER TABLE `termine`
                  ADD COLUMN `topic_id` VARCHAR(32) COLLATE latin1_bin DEFAULT NULL";
        DBManager::get()->exec($query);
    }

    protected function columnExists(string $table, string $column): bool
    {
        $query = "SHOW COLUMNS FROM `{$table}` LIKE ?";
        return (bool) DBManager::get()->fetchOne($query, [$column]);
    }
}
