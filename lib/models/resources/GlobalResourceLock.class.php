<?php

/**
 * GlobalResourceLock.class.php - model class for resource locks
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @copyright   2017-2019
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     resources
 * @since       4.5
 *
 * @property string $id alias column for lock_id
 * @property string $lock_id database column
 * @property int $begin database column
 * @property int $end database column
 * @property string $type database column
 * @property string $user_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 */
class GlobalResourceLock extends SimpleORMap
{
    protected static $defined_types = [];
    
    public function __construct($id = null)
    {
        self::initDefinedTypes();
        parent::__construct($id);
    }
    
    protected static function initDefinedTypes()
    {
        if (empty(self::$defined_types)) {
            self::$defined_types = [
                'default'        => _('Allgemeine Sperrung'),
                'planning'       => _('Planungsphase'),
                'reorganisation' => _('Reorganisation')
            ];
        }
    }
    
    protected static function configure($config = [])
    {
        $config['db_table'] = 'global_resource_locks';
        
        parent::configure($config);
    }
    
    public static function isLocked($begin, $end)
    {
        return self::countBySql('begin < :end AND end > :begin', compact('begin', 'end')) > 0;
    }

    /**
     * Returns a list of defined lock types.
     *
     * @return string[] An associative array with all defined lock types.
     */
    public static function getDefinedTypes()
    {
        self::initDefinedTypes();
        return self::$defined_types;
    }
    
    /**
     * Returns a string representation of the type of this resource lock.
     *
     * @return string A string representing the type of this resource lock.
     */
    public function getTypeString()
    {
        if (array_key_exists($this->type, self::$defined_types)) {
            return self::$defined_types[$this->type];
        } else {
            if ($this->type) {
                return _('Grund unbekannt');
            } else {
                return _('Grund nicht angegeben');
            }
        }
    }
}
