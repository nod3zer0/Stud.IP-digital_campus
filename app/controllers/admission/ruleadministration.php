<?php

/**
 * Admission_RuleadministrationController - Global administration
 * of available admission rules
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Thomas Hackl <thomas.hackl@uni-passau.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.0
 */

class Admission_RuleadministrationController extends AuthenticatedController
{
    /**
     * @see AuthenticatedController::before_filter
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        $GLOBALS['perm']->check('root');

        Navigation::activateItem('/admin/config/admissionrules');
        PageLayout::addScript('studip-admission.js');

        $sidebar = Sidebar::Get();

        $views = new ViewsWidget();
        $views->addLink(
            _('Installierte Anmelderegeln'),
            $this->url_for('admission/ruleadministration')
        )->setActive($action === 'index');
        $views->addLink(
            _('Regelkompatibilität'),
            $this->url_for('admission/ruleadministration/compatibility')
        )->setActive($action === 'compatibility');
        $sidebar->addWidget($views);
    }

    /**
     * Show overview of available admission rules.
     */
    public function index_action()
    {
        PageLayout::setTitle(_('Verwaltung von Anmelderegeln'));

        $this->ruleTypes = AdmissionRule::getAvailableAdmissionRules(false);

        // Available rule classes.
        $ruleClasses = array_map(function($s) {
            return mb_strtolower($s);
        }, array_keys($this->ruleTypes));

        // Found directories with rule definitions.
        $ruleDirs = array_map(function($s) {
            return basename($s);
        }, glob($GLOBALS['STUDIP_BASE_PATH'] . '/lib/admissionrules/*', GLOB_ONLYDIR));

        // Compare the two.
        $this->newRules = array_diff($ruleDirs, $ruleClasses);
        if (count($this->newRules) > 0) {
            PageLayout::postInfo(
                _('Es wurden Anmelderegeln gefunden, die zwar im'
                . 'Dateisystem unter lib/admissionrules vorhanden sind, aber noch nicht '
                . 'installiert wurden:'),
                $this->newRules
            );
        }
    }

    public function compatibility_action()
    {
        PageLayout::setTitle(_('Anmelderegelkompatibilität'));

        $this->ruletypes = AdmissionRule::getAvailableAdmissionRules(false);
        $this->matrix = AdmissionRuleCompatibility::getCompatibilityMatrix();
    }

    /**
     * Shows where the given admission rule is activated (system wide or
     * only at specific institutes).
     *
     * @param String $ruleType Class name of the rule type to check.
     */
    public function toggle_activation_action($ruleType)
    {
        $query = "UPDATE `admissionrules`
                  SET `active` = !`active`
                  WHERE `ruletype` = ?";
        DBManager::get()->execute($query, [$ruleType]);

        $this->redirect('admission/ruleadministration');
    }

    public function save_compat_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        // Iterate over existing entries and check which ones must be deleted.
        $matrix = AdmissionRuleCompatibility::getCompatibilityMatrix();

        $values = Request::getArray('compat');

        $to_delete = [];
        $new = [];
        foreach ($matrix as $type => $compat) {
            /*
             * Get entries that are in database, but not in request.
             * These must be removed from database as they are not
             * set anymore.
             */
            $to_delete[$type] = array_diff($compat, $values[$type]);

            /*
             * Get entries that are in request data, but not in database.
             * These must be inserted into DB.
             */
            $new[$type] = array_diff($values[$type], $compat);
        }

        // Get types that are set in request but not present at all in DB.
        foreach (array_diff(array_keys($values), array_keys($matrix)) as $newtype) {
            $new[$newtype] = $values[$newtype];
        }

        // Get types that are set in matrix but not present at all in request.
        foreach (array_diff(array_keys($matrix), array_keys($values)) as $oldtype) {
            $to_delete[$oldtype] = $matrix[$oldtype];
        }

        $success = 0;
        $fail = [];

        // Process the entries that will be deleted.
        foreach ($to_delete as $type => $compat) {
            foreach ($compat as $ctype) {
                $entry = AdmissionRuleCompatibility::find([$type, $ctype]);

                if ($entry->delete()) {
                    $success++;
                } else {
                    $fail[] = $type . ' => ' . $entry;
                }
            }
        }

        // Process the new entries.
        foreach ($new as $type => $entries) {
            foreach ($entries as $entry) {
                $a = new AdmissionRuleCompatibility();
                $a->rule_type = $type;
                $a->compat_rule_type = $entry;

                if ($a->store()) {
                    $success++;
                } else {
                    $fail[] = $type . ' => ' . $entry;
                }
            }

        }

        if ($success > 0 && count($fail) == 0) {
            PageLayout::postSuccess(_('Die Einstellungen zur Regelkompatibilität wurden gespeichert.'));
        } else if ($success > 0 && count($fail) > 0) {
            PageLayout::postWarning(_('Die Einstellungen zur '.
                'Regelkompatibilität konnten nicht vollständig gespeichert '.
                'werden. Es sind Probleme bei folgenden Einträgen aufgetreten:'),
                $fail);
        } else if (count($fail) > 0) {
            PageLayout::postError(_('Die Einstellungen zur Regelkompatibilität konnten nicht gespeichert werden.'));
        }

        $this->relocate('admission/ruleadministration/compatibility');
    }

    /**
     * Validate ticket (passed via request environment).
     * This method always checks Request::quoted('ticket').
     *
     * @throws InvalidArgumentException  if ticket is not valid
     */
    private function check_ticket()
    {
        if (!check_ticket(Request::option('ticket'))) {
            throw new InvalidArgumentException(_('Das Ticket für diese Aktion ist ungültig.'));
        }
    }
}
