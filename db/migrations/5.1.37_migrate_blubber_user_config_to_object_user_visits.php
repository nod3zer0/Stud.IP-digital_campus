<?php
final class MigrateBlubberUserConfigToObjectUserVisits extends Migration
{
    public function description()
    {
        return 'Migrates the blubber visited entrires from config_values to user_entries';
    }

    protected function up()
    {
        $query = "SELECT `pluginid`
                  FROM `plugins`
                  WHERE `pluginclassname` = 'Blubber'";
        $blubber_plugin_id = DBManager::get()->fetchColumn($query);

        $query = "INSERT INTO `object_user_visits` (
                     `object_id`,
                     `user_id`,
                     `plugin_id`,
                     `visitdate`,
                     `last_visitdate`
                  )
                  SELECT SUBSTR(`field`, 23) AS `object_id`,
                     `range_id` AS `user_id`,
                     ? AS `plugin_id`,
                     `value` AS `visitdate`,
                     `value` AS `last_visitdate`
                  FROM `config_values`
                  WHERE `field` LIKE 'BLUBBERTHREAD\\_VISITED\\_%'";
        DBManager::get()->execute($query, [$blubber_plugin_id]);

        $query = "DELETE FROM `config_values`
                  WHERE `field` LIKE 'BLUBBERTHREAD\\_VISITED\\_%'";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "SELECT `pluginid`
                  FROM `plugins`
                  WHERE `pluginclassname` = 'Blubber'";
        $blubber_plugin_id = DBManager::get()->fetchColumn($query);

        $query = "INSERT INTO `config_values` (
                     `field`,
                     `range_id`,
                     `value`,
                     `mkdate`,
                     `chdate`,
                     `comment`
                  )
                  SELECT CONCAT('BLUBBERTHREAD_VISITED_', `object_id`) AS `field`,
                     `user_id` AS `range_id`,
                     `visitdate` AS `value`,
                     UNIX_TIMESTAMP() AS `mkdate`,
                     UNIX_TIMESTAMP() AS `chdate`,
                     '' AS `comment`
                  FROM `object_user_visits`
                  WHERE `plugin_id` = ?";
        DBManager::get()->execute($query, [$blubber_plugin_id]);

        $query = "DELETE FROM `object_user_visits`
                  WHERE `plugin_id` = ?";
        DBManager::get()->execute($query, [$blubber_plugin_id]);
    }
}
