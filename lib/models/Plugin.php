<?php

/**
 * @property int $id
 * @property int $pluginid
 * @property string $pluginclassname
 * @property string $pluginpath
 * @property string $pluginname
 * @property string $plugintype
 * @property string $enabled
 * @property int $navigationpos
 * @property int|null $dependentonid
 * @property string|null $automatic_update_url
 * @property string|null $automatic_update_secret
 */
class Plugin extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'plugins';
        $config['i18n_fields'] = ['description', 'highlight_text'];
        parent::configure($config);
    }

}
