<?php

/**
 * @license GPL2 or any later version
 *
 * @property string $id alias column for identifier
 * @property string $identifier database column
 * @property string|null $name database column
 * @property string|null $link database column
 * @property int|null $default database column
 * @property string|null $description database column
 * @property string|null $twillo_licensekey database column
 * @property string|null $twillo_cclicenseversion database column
 * @property int|null $chdate database column
 * @property int|null $mkdate database column
 */
class License extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'licenses';
        parent::configure($config);
    }

    public static function findDefault()
    {
        return static::findOneBySQL("`default` = '1'");
    }
}
