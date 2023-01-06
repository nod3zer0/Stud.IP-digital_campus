<?php

class ChangeCwConfig extends Migration
{
    public function description()
    {
        return 'change courseware config';
    }

    public function up()
    {
        $db = DBManager::get();
        $query = "UPDATE `config` SET `value` = '{}', `type` = 'array' WHERE `config`.`field` = 'COURSEWARE_SEQUENTIAL_PROGRESSION'";
        $db->exec($query);
        
        $query = "UPDATE `config` SET `value` = '{}', `type` = 'array' WHERE `config`.`field` = 'COURSEWARE_EDITING_PERMISSION'";
        $db->exec($query);
        
        
        $update_permission = $db->prepare("UPDATE `config_values` SET `value` = ? WHERE `field` = 'COURSEWARE_EDITING_PERMISSION' AND `range_id` = ?");
        
        $find_root = $db->prepare("SELECT * FROM `cw_structural_elements` WHERE `parent_id` IS NULL AND `range_id` = ? ");
        
        
        // get all COURSEWARE_EDITING_PERMISSION 
        $stmt = $db->prepare("SELECT * FROM `config_values` WHERE `field` = 'COURSEWARE_EDITING_PERMISSION'");
        $stmt->execute();
        $cw_permissions = $stmt->fetchAll();

        foreach ($cw_permissions as $permission) {
            $find_root->execute([$permission['range_id']]);
            $root = $find_root->fetchAll();
            $value = json_encode([$root[0]['id'] => $permission['value']], true);
            $update_permission->execute([$value, $permission['range_id']]);
        }

        $update_progression = $db->prepare("UPDATE `config_values` SET `value` = ? WHERE `field` = 'COURSEWARE_SEQUENTIAL_PROGRESSION' AND `range_id` = ?");
        
        // get all COURSEWARE_SEQUENTIAL_PROGRESSION 
        $stmt = $db->prepare("SELECT * FROM `config_values` WHERE `field` = 'COURSEWARE_SEQUENTIAL_PROGRESSION'");
        $stmt->execute();
        $cw_progressions = $stmt->fetchAll();
        
        foreach ($cw_progressions as $progression) {
            $find_root->execute([$progression['range_id']]);
            $root = $find_root->fetchAll();
            $value = json_encode([$root[0]['id'] => $progression['value']], true);
            $update_progression->execute([$value, $progression['range_id']]);
        }
    }

    public function down()
    {
        $db = \DBManager::get();
    }
}
