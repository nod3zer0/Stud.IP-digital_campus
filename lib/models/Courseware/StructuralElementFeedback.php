<?php

namespace Courseware;

use User;

/**
 * Courseware's feedback on structural elements.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.1
 *
 * @property int                            $id                      database column
 * @property int                            $structural_element_id   database column
 * @property string                         $user_id                 database column
 * @property string                         $feedback                 database column
 * @property int                            $mkdate                  database column
 * @property int                            $chdate                  database column
 * @property \User                          $user                    belongs_to User
 * @property \Courseware\StructuralElement  $structural_element      belongs_to Courseware\StructuralElement
 */
class StructuralElementFeedback extends \SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'cw_structural_element_feedbacks';

        $config['belongs_to']['user'] = [
            'class_name' => User::class,
            'foreign_key' => 'user_id',
        ];

        $config['belongs_to']['structural_element'] = [
            'class_name' => StructuralElement::class,
            'foreign_key' => 'structural_element_id',
        ];

        parent::configure($config);
    }
}
