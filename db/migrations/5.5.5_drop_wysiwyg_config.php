<?php

class DropWysiwygConfig extends Migration
{
    public function description()
    {
        return 'Drop config option for disabling the ckeditor';
    }

    public function up()
    {
        $settings = [
            'WYSIWYG',
            'WYSIWYG_DISABLED'
        ];

        $query = 'DELETE `config`, `config_values`
                  FROM `config` LEFT JOIN `config_values` USING (`field`)
                  WHERE `field` IN (?)';
        DBManager::get()->execute($query, [$settings]);
    }

    public function down()
    {
        $query = 'INSERT INTO `config` (`field`, `value`, `type`, `range`, `section`, `mkdate`, `chdate`, `description`)
                  VALUES (:name, :value, :type, :range, :section, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), :description)';
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            ':name'        => 'WYSIWYG',
            ':description' => 'Aktiviert den WYSIWYG Editor im JavaScript.',
            ':section'     => 'global',
            ':range'       => 'global',
            ':type'        => 'boolean',
            ':value'       => '1'
        ]);

        // WYSIWYG_DISABLED did not exist as a setting in the DB
    }
}
