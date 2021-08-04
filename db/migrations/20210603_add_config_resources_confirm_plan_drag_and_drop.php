<?php


class AddConfigResourcesConfirmPlanDragAndDrop extends Migration
{
    public function description()
    {
        return 'Add configuration RESOURCES_CONFIRM_PLAN_DRAG_AND_DROP';
    }


    public function up()
    {
        $db = DBManager::get();

        $db->exec(
            "INSERT INTO `config`
            (`field`, `value`, `type`, `range`,
            `section`,
            `mkdate`, `chdate`,
            `description`)
            VALUES
            ('RESOURCES_CONFIRM_PLAN_DRAG_AND_DROP', '0', 'boolean', 'user',
            'resources', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
            'Soll beim Verschieben von Buchungen im Belegungsplan eine Sicherheitsabfrage erscheinen?')"
        );
    }


    public function down()
    {
        $db = DBManager::get();

        $db->exec(
            "DELETE FROM `config_values`
            WHERE `field` = 'RESOURCES_CONFIRM_PLAN_DRAG_AND_DROP'"
        );
        $db->exec(
            "DELETE FROM `config`
            WHERE `field` = 'RESOURCES_CONFIRM_PLAN_DRAG_AND_DROP'"
        );
    }
}
