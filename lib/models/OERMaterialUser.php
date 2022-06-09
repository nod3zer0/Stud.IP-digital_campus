<?php

class OERMaterialUser extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'oer_material_users';

        $config['belongs_to']['oeruser'] = [
            'class_name' => ExternalUser::class,
            'foreign_key' => 'user_id'
        ];

        $config['belongs_to']['material'] = [
            'class_name' => OERMaterial::class,
            'foreign_key' => 'material_id'
        ];
        parent::configure($config);
    }
}
