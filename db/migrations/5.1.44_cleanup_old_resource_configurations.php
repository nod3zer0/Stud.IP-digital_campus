<?php

/**
 * @see https://gitlab.studip.de/studip/studip/-/issues/2423
 */
final class CleanupOldResourceConfigurations extends Migration
{
    public function description()
    {
        return 'Removes obsolete configuration entries from old resource management';
    }

    protected function up()
    {
        $query = "DELETE `config`, `config_values`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` IN (
                      'RESOURCES_ALLOW_CREATE_ROOMS',
                      'RESOURCES_ALLOW_CREATE_TOP_LEVEL',
                      'RESOURCES_ALLOW_DELETE_REQUESTS',
                      'RESOURCES_ALLOW_REQUESTABLE_ROOM_REQUESTS',
                      'RESOURCES_ALLOW_ROOM_REQUESTS_ALL_ROOMS',
                      'RESOURCES_ENABLE_GROUPING',
                      'RESOURCES_ENABLE_ORGA_CLASSIFY',
                      'RESOURCES_ENABLE_SEM_SCHEDULE',
                      'RESOURCES_ENABLE_VIRTUAL_ROOM_GROUPS',
                      'RESOURCES_HIDE_PAST_SINGLE_DATES',
                      'RESOURCES_INHERITANCE_PERMS',
                      'RESOURCES_INHERITANCE_PERMS_ROOMS',
                      'RESOURCES_LOCKING_ACTIVE',
                      'RESOURCES_ROOM_REQUEST_DEFAULT_ACTION',
                      'RESOURCES_SCHEDULE_EXPLAIN_USER_NAME',
                      'RESOURCES_SEARCH_ONLY_REQUESTABLE_PROPERTY',
                      'RESOURCES_SHOW_ROOM_NOT_BOOKED_HINT',
                      'RESOURCES_ENABLE_ORGA_ADMIN_NOTICE'
                  )";
        DBManager::get()->exec($query);
    }
}
