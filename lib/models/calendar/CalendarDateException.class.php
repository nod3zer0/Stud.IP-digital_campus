<?php
/**
 * The CalendarDateException class represents one exception for a calendar date.
 * 
 * This file is part of Stud.IP
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2023
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     resources
 * @since       5.5
 *
 * @property string $id The ID of the exception.
 * @property string $calendar_date_id The ID of the calendar date where the exception belongs to.
 * @property string $date The date of the exception in the date format YYYY-MM-DD.
 * @property string $mkdate The creation date of the exception.
 * @property string $chdate The modification date of the exception.
 * @property CalendarDate|null $calendar_date The associated calendar date object.
 */
class CalendarDateException extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'calendar_date_exceptions';

        $config['belongs_to']['calendar_date'] = [
            'class_name' => CalendarDate::class,
            'foreign_key' => 'calendar_date_id',
            'assoc_func' => 'find'
        ];

        parent::configure($config);
    }
}
