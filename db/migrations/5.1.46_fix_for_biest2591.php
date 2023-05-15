<?php
final class FixForBiest2591 extends Migration
{
    protected function up()
    {
        $query = "DELETE `tools_activated`
                  FROM `tools_activated`
                  JOIN `seminare` ON `range_id` = `Seminar_id`
                  WHERE `seminare`.`status` IN (
                    SELECT `id`
                    FROM `sem_classes`
                    WHERE `studygroup_mode` = 1
                  ) AND `plugin_id` IN (
                    SELECT `pluginid`
                    FROM `plugins`
                    WHERE `pluginclassname` = 'CoreParticipants'
                  )";
        DBManager::get()->exec($query);
    }
}
