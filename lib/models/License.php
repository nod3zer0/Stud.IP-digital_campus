<?php

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
