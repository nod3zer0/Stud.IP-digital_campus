<?php

namespace Courseware;

/**
* Courseware's template.
*
* @author  Ron Lucke <lucke@elan-ev.de>
* @license GPL2 or any later version
*
* @since   Stud.IP 5.1
*
 *
 * @property int $id database column
 * @property string $name database column
 * @property string|null $purpose database column
 * @property string $structure database column
 * @property int $mkdate database column
 * @property int $chdate database column
*/
class Template extends \SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_templates';

        parent::configure($config);
    }
}
