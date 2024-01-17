<?php

/**
 * Wikiversion.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Rasmus Fuhse <fuhse@data-quest.de>
 * @copyright   2023 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property string page_id       database column
 * @property string id            alias column for user_id
 * @property string last_lifesign computed column read/write
 */
class WikiVersion extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'wiki_versions';
        $config['belongs_to']['page'] = [
            'class_name'  => WikiPage::class,
            'foreign_key' => 'page_id'
        ];
        $config['belongs_to']['user'] = [
            'class_name'  => User::class,
            'foreign_key' => 'user_id'
        ];
        $config['additional_fields']['predecessor'] = [
            'get' => function ($version) {
                return static::findOneBySQL('`page_id` = :page_id AND `mkdate` < :version_time ORDER BY `mkdate` DESC LIMIT 1', [
                    'page_id' => $version['page_id'],
                    'version_time' => $version['mkdate']
                ]);
            }
        ];
        $config['additional_fields']['successor'] = [
            'get' => function ($version) {
                $newer_version = static::findOneBySQL('`page_id` = :page_id AND `mkdate` > :version_time ORDER BY `mkdate` ASC LIMIT 1', [
                    'page_id' => $version['page_id'],
                    'version_time' => $version['mkdate']
                ]);
                return $newer_version ?? $version->page;
            }
        ];
        $config['additional_fields']['versionnumber'] = [
            'get' => function ($version) {
                $i = 1;
                foreach (array_reverse($version->page->versions->getArrayCopy()) as $v) {
                    if ($v->id === $version->id) {
                        return $i;
                    }
                    $i++;
                }
                return null;
            }
        ];
        parent::configure($config);
    }
}
