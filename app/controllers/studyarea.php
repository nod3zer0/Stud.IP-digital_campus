<?php

/**
 * treenode.php - Controller for editing tree nodes
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <hackl@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2 or later
 * @category    Stud.IP
 * @since       5.4
 */

class StudyareaController extends AuthenticatedController
{
    public function edit_action($id = '')
    {
        if ($id !== '') {
            $object = StudipStudyArea::find($id);
        } else {
            $object = new StudipStudyArea();
        }

        PageLayout::setTitle($object->isNew() ? _('Studienbereich anlegen') : _('Studienbereich bearbeiten'));

        $this->form = Studip\Forms\Form::fromSORM(
            $object,
            [
                'legend' => $object->isNew()
                    ? _('Neuer Studienbereich')
                    : sprintf(_('Studienbereich %s'), $object->name),
                'text' => ['text' => ''],
                'fields' => [
                    'name' => [
                        'label' => _('Name'),
                        'type' => 'text',
                        'required' => true
                    ],
                    'info' => [
                        'label' => _('Beschreibung'),
                        'type' => 'textarea'
                    ]
                ]
            ]
        )->setURL($this->url_for('studyarea/store', $object->id));
    }

}
