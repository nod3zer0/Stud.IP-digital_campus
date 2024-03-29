<?php
/**
 * CacheType.php
 * model class for registered cache types
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <studip@thomas-hackl.name>
 * @copyright   2020 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 *
 * @property int $id alias column for cache_id
 * @property int $cache_id database column
 * @property string $class_name database column
 * @property int|null $chdate database column
 * @property int|null $mkdate database column
 * @property mixed $display_name additional field
 */

class CacheType extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cache_types';

        $config['additional_fields']['display_name'] = true;

        parent::configure($config);
    }

    public function getDisplay_name(): string
    {
        return $this->class_name::getDisplayName();
    }

}
