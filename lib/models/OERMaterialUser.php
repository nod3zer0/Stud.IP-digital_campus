<?php

/**
 * @license GPL2 or any later version
 *
 * @property array $id alias for pk
 * @property string $material_id database column
 * @property string $user_id database column
 * @property int $external_contact database column
 * @property int $position database column
 * @property int $chdate database column
 * @property int $mkdate database column
 * @property ExternalUser $oeruser belongs_to ExternalUser
 * @property OERMaterial $material belongs_to OERMaterial
 */
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
