<?php
/**
 * DatafieldEntryModelI18N
 * model class for table datafields_entries
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @copyright   2017 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.1
 *
 * @property array $id alias for pk
 * @property string $datafield_id database column
 * @property string $range_id database column
 * @property I18NString|null $content database column
 * @property int|null $mkdate database column
 * @property int|null $chdate database column
 * @property string $sec_range_id database column
 * @property string $lang database column
 * @property DataField $datafield belongs_to DataField
 * @property mixed $name additional field
 */

class DatafieldEntryModelI18N extends DatafieldEntryModel
{
    protected static function configure($config = [])
    {
        $config['i18n_fields']['content'] = true;
        parent::configure($config);
    }
}
