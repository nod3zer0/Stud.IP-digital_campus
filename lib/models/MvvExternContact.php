<?php
/**
 * MvvExternContact.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Timo Hartge <hartge@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.5
 *
 * @property string $id alias column for extern_contact_id
 * @property string $extern_contact_id database column
 * @property I18NString $name database column
 * @property string|null $vorname database column
 * @property I18NString $homepage database column
 * @property string $mail database column
 * @property string $tel database column
 * @property string|null $author_id database column
 * @property string|null $editor_id database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property MvvContact $MvvContact belongs_to MvvContact
 */

class MvvExternContact extends ModuleManagementModel
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'mvv_extern_contacts';

        $config['belongs_to']['MvvContact'] = [
            'class_name' => MvvContact::class,
            'foreign_key' => 'extern_contact_id',
            'assoc_func' => 'findCached',
        ];

        $config['i18n_fields']['name']     = true;
        $config['i18n_fields']['homepage'] = true;

        parent::configure($config);
    }

    protected function logChanges($action = null)
    {
        $log_action = 'MVV_EXTERN_CONTACT_' . mb_strtoupper($action);
        $affected = $this->id;
        $info = ['mvv_extern_contacts.*'];
        $debug_info = $this->getDisplayName();
        if ($action === 'update') {
            $logged_fields = [
                'name',
                'vorname',
                'homepage',
                'mail',
                'tel',
            ];
            foreach ($logged_fields as $logged_field) {
                if ($this->isFieldDirty($logged_field)) {
                    $info[] = $logged_field
                        . ': ' . ($this->getValue($logged_field) ?? '-')
                        . ' (' . ($this->getPristineValue($logged_field) ?? '-')
                        . ')';
                }
            }
        }
        StudipLog::log($log_action, $affected, null, implode(' | ', $info), $debug_info);
    }
}
