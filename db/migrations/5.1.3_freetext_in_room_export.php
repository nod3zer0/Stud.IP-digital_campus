<?php
class FreetextInRoomExport extends Migration
{
    public function description()
    {
        return 'Adds a global freetext for placing in room exports';
    }

    protected function up()
    {
        $query = "INSERT IGNORE INTO `config` (
                `field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`
            ) VALUES (
                'RESOURCES_ADDITIONAL_TEXT_ROOM_EXPORT', '', 'string', 'global', 'resources', UNIX_TIMESTAMP(),
                UNIX_TIMESTAMP(), 'Zusatztext, der beim Seriendruck unter jedem Raumplan angezeigt werden soll'
            )";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "DELETE FROM `config` WHERE `field` = 'RESOURCES_ADDITIONAL_TEXT_ROOM_EXPORT'";
        DBManager::get()->exec($query);
        $query = "DELETE FROM `config_values` WHERE `field` = 'RESOURCES_ADDITIONAL_TEXT_ROOM_EXPORT'";
        DBManager::get()->exec($query);
    }
}
