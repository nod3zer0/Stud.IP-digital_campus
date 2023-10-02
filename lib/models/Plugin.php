<?php

/**
 *
 * @property int $id alias column for pluginid
 * @property int $pluginid database column
 * @property string $pluginclassname database column
 * @property string $pluginpath database column
 * @property string $pluginname database column
 * @property string $plugintype database column
 * @property string $enabled database column
 * @property int $navigationpos database column
 * @property int|null $dependentonid database column
 * @property string|null $automatic_update_url database column
 * @property string|null $automatic_update_secret database column
 * @property I18NString|null $description database column
 * @property string|null $description_mode database column
 * @property int|null $highlight_until database column
 * @property I18NString|null $highlight_text database column
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
