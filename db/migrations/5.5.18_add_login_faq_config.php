<?php


class AddLoginFaqConfig extends Migration
{
    public function description()
    {
        return 'Creates configs for login faq: Visibility and title (eg.: Hilfe zum Login)';
    }

    public function up()
    {
        $query = 'INSERT INTO `config` (`field`, `value`, `type`, `section`, `range`, `description`, `mkdate`, `chdate`)
                  VALUES (:name, :value, :type, :section, :range, :description, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())';
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            'name'          => 'LOGIN_FAQ_TITLE',
            'value'         => 'Hinweise zum Login',
            'type'          => 'i18n',
            'section'       => 'Loginseite',
            'range'         => 'global',
            'description'   => 'Überschrift für den FAQ-Bereich auf der Loginseite'
        ]);

        $statement->execute([
            'name'          => 'LOGIN_FAQ_VISIBILITY',
            'value'         => '1',
            'type'          => 'boolean',
            'section'       => 'Loginseite',
            'range'         => 'global',
            'description'   => 'Soll der FAQ-Bereich auf der Loginseite sichtbar sein?'
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
                       'LOGIN_FAQ_TITLE',
                       'LOGIN_FAQ_VISIBILITY',
                       'USERNAME_TOOLTIP_ACTIVATED',
                       'PASSWORD_TOOLTIP_ACTIVATED'
                  )";
        DBManager::get()->exec($query);
    }
}
