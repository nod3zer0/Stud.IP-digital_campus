<?php
/**
 * @see https://gitlab.studip.de/studip/studip/-/issues/3282
 */
final class CleanupResourceProperties extends Migration
{
    public function description()
    {
        return 'Removes orphaned rows from table "resource_properties" with no '
             . 'definition in table "resource_property_definitions"';
    }

    protected function up()
    {
        $query = "DELETE FROM `resource_properties`
                  WHERE `property_id` NOT IN (
                      SELECT `property_id`
                      FROM `resource_property_definitions`
                  )";
        DBManager::get()->exec($query);
    }
}
