<?php
/**
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @see    https://develop.studip.de/trac/ticket/11418
 */
class Tic11418StudygroupTermsI18n extends Migration
{
    public function description()
    {
        return 'Changes config type to i18n for field STUDYGROUP_TERMS';
    }

    protected function up()
    {
        $query = "UPDATE `config`
                  SET `type` = 'i18n'
                  WHERE `field` = 'STUDYGROUP_TERMS'";
        DBManager::get()->exec($query);
    }

    protected function down()
    {
        $query = "DELETE FROM `i18n`
                  WHERE `object_id` = MD5('STUDYGROUP_TERMS')
                    AND `table` = 'config'
                    AND `field` = 'value'";
        DBManager::get()->exec($query);

        $query = "UPDATE `config`
                  SET `type` = 'string'
                  WHERE `field` = 'STUDYGROUP_TERMS'";
        DBManager::get()->exec($query);
    }
}
