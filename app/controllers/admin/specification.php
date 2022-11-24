<?php
# Lifter010: TODO
/**
 * specification.php - controller class for the specification
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Nico Müller <nico.mueller@uni-oldenburg.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 * @since       Stud.IP version 2.1
 */
class Admin_SpecificationController extends AuthenticatedController
{
    protected $_autobind = true;

    /**
     * Common tasks for all actions.
     */
    public function before_filter(&$action, &$args)
    {
        global $perm;

        parent::before_filter($action, $args);

        # user must have special permission
        if (!$perm->have_perm(Config::get()->AUX_RULE_ADMIN_PERM ?: 'admin')) {
            throw new AccessDeniedException();
        }

        //setting title and navigation
        Navigation::activateItem('/admin/config/specification');
        PageLayout::setTitle(_('Verwaltung von Zusatzangaben'));
    }

    /**
     * Maintenance view for the specification parameters
     */
    public function index_action()
    {
        $this->rules = AuxLockRule::findBySQL('1 ORDER BY name');

        Sidebar::Get()->addWidget(new ActionsWidget())->addLink(
            _('Neue Regel anlegen'),
            $this->editURL(),
            Icon::create('add')
        );
    }

    /**
     * Edit or create a rule
     * @property AuxLockRule $rule
     */
    public function edit_action(AuxLockRule $rule = null)
    {
        $rule->name = Request::i18n('name', $rule->name);
        $rule->description = Request::i18n('description', $rule->description);
        $rule->attributes = Request::optionArray('fields') ?: $rule->attributes;
        $rule->sorting = Request::getArray('order') ?: $rule->sorting;

        if ($GLOBALS['perm']->have_perm('root')) {
            Sidebar::Get()->addWidget(new ActionsWidget())->addLink(
                _('Datenfelder bearbeiten'),
                URLHelper::getURL('dispatch.php/admin/datafields'),
                Icon::create('edit')
            );
        }

        $this->semFields       = $this->getSemFields();
        $this->entries_user    = DataField::getDataFields('user');
        $this->entries_semdata = DataField::getDataFields('usersemdata');

        if ($GLOBALS['perm']->have_perm('root') && count($this->entries_semdata) === 0) {
            PageLayout::postWarning(sprintf(
                _('Sie müssen zuerst im Bereich %sDatenfelder%s in der Kategorie '
                . '<em>Datenfelder für Personenzusatzangaben in Veranstaltungen</em> '
                . 'einen neuen Eintrag erstellen.'),
                '<a href="' . URLHelper::getLink('dispatch.php/admin/datafields') . '">',
                '</a>'
            ));
        }
    }

    /**
     * Store or edit Rule
     * @param string $id
     */
    public function store_action(AuxLockRule $rule = null)
    {
        CSRFProtection::verifyUnsafeRequest();

        $errors = [];
        if (!trim(Request::get('name'))) {
            $errors[] = _('Bitte geben Sie der Regel mindestens einen Namen!');
        }

        if (!AuxLockRule::validateFields(Request::optionArray('fields'))) {
            $errors[] = _('Bitte wählen Sie mindestens ein Feld aus der Kategorie "Zusatzinformationen" aus!');
        }

        if ($errors) {
            PageLayout::postError(_('Ihre Eingaben sind ungültig.'), $errors);
            $this->keepRequest();
            $this->redirect($this->editURL($rule));
        } else {
            $rule->name = Request::i18n('name');
            $rule->description = Studip\Markup::purifyHtml(Request::i18n('description'));
            $rule->attributes = Request::optionArray('fields') ?? [];
            $rule->sorting = Request::getArray('order') ?? [];

            if ($rule->store()) {
                PageLayout::postSuccess(sprintf(
                    _('Die Regel "%s" wurde erfolgreich gespeichert!'),
                    htmlReady($rule->name)
                ));
            }
            $this->redirect('admin/specification');
        }
    }

    /**
     * Delete a rule, using a modal dialog
     */
    public function delete_action(AuxLockRule $rule)
    {
        CSRFProtection::verifyUnsafeRequest();

        $result = $rule->delete();
        if ($result === false) {
            PageLayout::postError(_('Es können nur nicht verwendete Regeln gelöscht werden!'));
        } elseif ($result > 0) {
            PageLayout::postSuccess(_('Die Regel wurde erfolgreich gelöscht!'));
        }

        $this->redirect($this->indexURL());
    }

    private function getSemFields(): array
    {
        return [
            'vasemester' => _('Semester'),
            'vanr'       => _('Veranstaltungsnummer'),
            'vatitle'    => _('Veranstaltungstitel'),
            'vadozent'   => _('Dozent'),
        ];
    }
}
