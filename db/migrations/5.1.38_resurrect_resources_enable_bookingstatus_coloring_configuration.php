<?php
/**
 * @see https://gitlab.studip.de/studip/studip/-/issues/1983
 */
final class ResurrectResourcesEnableBookingstatusColoringConfiguration extends Migration
{
    public function description()
    {
        return 'Resurrects the missing RESOURCES_ENABLE_BOOKINGSTATUS_COLORING configuration';
    }

    protected function up()
    {
        $query = "INSERT IGNORE INTO `config` (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
                  VALUES('RESOURCES_ENABLE_BOOKINGSTATUS_COLORING', '1', 'boolean', 'global', 'resources', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'Enable the colored presentation of the room booking status of a date')";
        DBManager::get()->exec($query);
    }
}
