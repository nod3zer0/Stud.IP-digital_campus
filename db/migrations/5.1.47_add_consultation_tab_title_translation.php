<?php
final class AddConsultationTabTitleTranslation extends Migration
{
    public function description()
    {
        return 'Adds the missing default translation for configuration CONSULTATION_TAB_TITLE';
    }

    protected function up()
    {
        $query = "INSERT IGNORE INTO `i18n` (`object_id`, `table`, `field`, `lang`, `value`)
                  VALUES (MD5('CONSULTATION_TAB_TITLE'), 'config', 'value', 'en_GB', 'Date allocation')";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "DELETE FROM `i18n`
                  WHERE `object_id` = MD5('CONSULTATION_TAB_TITLE')
                    AND `table` = 'config'
                    AND `field` = 'value'
                    AND `lang` = 'en_GB'
                    AND `value` = 'Date allocation'";
        DBManager::get()->exec($query);
    }
}
