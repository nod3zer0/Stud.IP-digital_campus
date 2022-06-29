<?php
/**
 * @see https://gitlab.studip.de/studip/studip/-/issues/1224
 * @see https://gitlab.studip.de/studip/studip/-/issues/881
 */
final class RemoveColumnExTermineTopicId extends Migration
{
    public function description()
    {
        return 'Removes unused column topic_id from table ex_termine.';
    }

    protected function up()
    {
        if (!$this->columnExists('ex_termine', 'topic_id')) {
            $this->write("Column ex_termine.topic_id does not exist");
            return;
        }

        $query = "ALTER TABLE `ex_termine`
                  DROP COLUMN `topic_id`";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        if ($this->columnExists('ex_termine', 'topic_id')) {
            $this->write("Column ex_termine.topic_id already exists");
            return;
        }

        $query = "ALTER TABLE `ex_termine`
                  ADD COLUMN `topic_id` VARCHAR(32) COLLATE latin1_bin DEFAULT NULL";
        DBManager::get()->exec($query);
    }

    protected function columnExists(string $table, string $column): bool
    {
        $query = "SHOW COLUMNS FROM `{$table}` LIKE ?";
        return (bool) DBManager::get()->fetchOne($query, [$column]);
    }
}
