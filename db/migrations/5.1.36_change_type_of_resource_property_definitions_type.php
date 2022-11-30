<?php
final class ChangeTypeOfResourcePropertyDefinitionsType extends Migration
{
    public function description()
    {
        return 'Changes the type of resource_property_definitions.type to ENUM';
    }

    protected function up()
    {
        $query = "ALTER TABLE `resource_property_definitions`
                  CHANGE COLUMN  `type` `type` ENUM('bool', 'text', 'num', 'select', 'user', 'institute', 'position', 'fileref', 'url') COLLATE latin1_bin NOT NULL";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "ALTER TABLE `resource_property_definitions`
                  CHANGE COLUMN  `type` `type` SET('bool', 'text', 'num', 'select', 'user', 'institute', 'position', 'fileref', 'url', 'resource_ref_list') COLLATE latin1_bin DEFAULT NULL";
        DBManager::get()->exec($query);
    }
}
