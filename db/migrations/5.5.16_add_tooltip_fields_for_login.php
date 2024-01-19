<?php


class AddTooltipFieldsForLogin extends Migration
{
    public function description()
    {
        return 'Creates config for login username and password tooltip texts';
    }

    public function up()
    {
        $query = 'INSERT INTO `config` (`field`, `value`, `type`, `section`, `range`, `description`, `mkdate`, `chdate`)
                  VALUES (:name, :value, :type, :section, :range, :description, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())';
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            'name'          => 'USERNAME_TOOLTIP_TEXT',
            'value'         => '',
            'type'          => 'i18n',
            'section'       => 'Loginseite',
            'range'         => 'global',
            'description'   => 'Text für den Tooltip des Benutzernamens auf der Loginseite'
        ]);

        $statement->execute([
            'name'          => 'PASSWORD_TOOLTIP_TEXT',
            'value'         => '',
            'type'          => 'i18n',
            'section'       => 'Loginseite',
            'range'         => 'global',
            'description'   => 'Text für den Tooltip des Benutzernamens auf der Loginseite'
        ]);

    }

    public function down()
    {
        $query = "DELETE `config`, `config_values`, `i18n`
                  FROM `config`
                  LEFT JOIN `config_values` USING (`field`)
                  LEFT JOIN `i18n`
                    ON `table` = 'config'
                        AND `field` = 'value'
                        AND `object_id` = MD5(`config`.`field`)
                  WHERE `field` IN (
                       'USERNAME_TOOLTIP_TEXT',
                       'PASSWORD_TOOLTIP_TEXT'
                  )";
        DBManager::get()->exec($query);
    }
}
