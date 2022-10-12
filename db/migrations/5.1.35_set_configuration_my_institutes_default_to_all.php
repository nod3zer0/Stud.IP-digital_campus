<?php

/**
 * @author Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license GPL2 or any later version
 *
 * @see https://develop.studip.de/trac/ticket/7058
 * @see https://gitlab.studip.de/studip/studip/-/issues/1656
 */
final class SetConfigurationMyInstitutesDefaultToAll extends Migration
{
    public function description()
    {
        return 'Changes the default value of configuration MY_INSTITUTES_DEFAULT to "all"';
    }

    protected function up()
    {
        $query = "UPDATE `config`
                  SET `value` = 'all'
                  WHERE `field` = 'MY_INSTITUTES_DEFAULT'";
        DBManager::get()->exec($query);
    }
}
