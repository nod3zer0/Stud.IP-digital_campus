<?php
/**
 * user.php - controller class for the user-administration
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Nico Müller <nico.mueller@uni-oldenburg.de>
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @author      David Siegfried <david.siegfried@uni-vechta.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     admin
 * @since       2.1
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_once 'vendor/email_message/blackhole_message.php';
require_once 'lib/statusgruppe.inc.php';

/**
 *
 * controller class for the user-administration
 *
 */
class Admin_UserController extends AuthenticatedController
{
    protected $_autobind = true;
    /**
     * Common tasks for all actions.
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // user must have root permission if restricted user management is disabled
        $GLOBALS['perm']->check(Config::get()->RESTRICTED_USER_MANAGEMENT ? 'root' : 'admin');

        // set navigation
        Navigation::activateItem('/admin/user/index');

        //PageLayout
        PageLayout::setHelpKeyword("Admins.Benutzerkonten");
        PageLayout::setTitle(_("Personenverwaltung"));

        $this->action = $action;
        $this->args   = $args;

        NotificationCenter::addObserver($this, 'addSidebar', 'SidebarWillRender');
    }

    /**
     * Display searchbox and all searched users (if any).
     *
     * @param bool $advanced open or close the advanced searchfields
     */
    public function index_action($advanced = false)
    {
        $this->perm = $GLOBALS['perm'];
        $request    = '';

        //Datafields
        $this->datafields = [];
        $datafields = DataField::getDataFields("user");
        foreach ($datafields as $datafield) {
            if ($datafield->accessAllowed()) {
                $this->datafields[] = $datafield;
            }
        }

        // roles
        $this->roles = array_filter(RolePersistence::getAllRoles(), function($role) {
            return !$role->systemtype;
        });

        //Daten annehmen
        if (Request::submitted('reset')) {
            unset($_SESSION['admin']['user']);
        } elseif (Request::submitted('search')) {
            $request = iterator_to_array(Request::getInstance());

            // Inaktivität für die suche anpassen
            $inaktiv = [$request['inaktiv'], $request['inaktiv_tage']];
            if (empty($request['inaktiv_tage']) && $request['inaktiv'] != 'nie') {
                $inaktiv = null;
            }

            //suche mit datafields
            $search_datafields = [];
            foreach ($this->datafields as $datafield) {
                if (!empty($request[$datafield->id])
                    && !(in_array($datafield->type, words('selectbox radio')) && $request[$datafield->id] === '---ignore---')
                ) {
                    $search_datafields[$datafield->id] = trim($request[$datafield->id]);
                }
            }

            $request['username']   = trim($request['username']);
            $request['email']      = trim($request['email']);
            $request['vorname']    = trim($request['vorname']);
            $request['nachname']   = trim($request['nachname']);
            $request['inaktiv']    = $inaktiv;
            $request['datafields'] = $search_datafields;

            $_SESSION['admin']['user'] = $request;
        } elseif (!empty($_SESSION['admin']['user']['results'])) {
            //Suchparameter und Ergebnisse vorhanden
            $request = $_SESSION['admin']['user'];
        }

        $this->request = [];
        $this->users = [];

        //wenn suche durchgeführt
        if (!empty($request)) {
            //Suchparameter
            $this->sortby = Request::option('sortby', 'username');
            $this->order  = Request::option('order', 'asc');
            if (Request::int('toggle')) {
                $this->order = $this->order == 'desc' ? 'asc' : 'desc';
            }

            $request['sortby'] = $this->sortby;
            $request['order'] = $this->order;

            $empty_search          = $request['perm'] === 'alle';

            $values = [
                'username',
                'vorname',
                'nachname',
                'email',
                'inaktiv',
                'locked',
                'show_only_not_lectures',
                'datafields',
                'inaktiv_tage',
                'institute',
                'studycourse',
                'degree',
                'fachsem',
                'userdomains',
                'auth_plugins',
            ];
            foreach ($values as $value) {
                if (!empty($request[$value])) {
                    $empty_search = false;
                    break;
                }
            }
            //Daten abrufen
            $this->request = $request;
            $this->users   = $empty_search ? false : User::search($request);

            // Fehler abfangen
            if ($this->users === false) {
                PageLayout::postInfo(_('Sie haben keine Suchkriterien ausgewählt!'));
            } elseif (count($this->users) < 1 && Request::submitted('search')) {
                PageLayout::postInfo(_('Es wurden keine Personen mit diesen Suchkriterien gefunden.'));
            } else {
                $_SESSION['admin']['user']['results'] = true;
                PageLayout::postInfo(sprintf(_('Es wurden %s Personen mit diesen Suchkriterien gefunden.'), count($this->users)));
            }
            if (is_array($this->users) && Request::submitted('export')) {
                $tmpname  = md5(uniqid('tmp'));
                $captions = ['username',
                             'vorname',
                             'nachname',
                             'email',
                             'status',
                             'authentifizierung',
                             'domänen',
                             'registriert seit',
                             'inaktiv seit'];
                foreach ($this->datafields as $datafield) {
                    $captions[] = $datafield->name;
                }
                $mapper   = function ($u) {
                    $userdomains = array_map(function ($ud) {
                        return $ud->name;
                    }, UserDomain::getUserDomainsForUser($u->id));
                    $data = [
                        $u['username'],
                        $u['Vorname'],
                        $u['Nachname'],
                        $u['Email'],
                        $u['perms'],
                        $u['auth_plugin'],
                        join(';', $userdomains),
                        $u['mkdate'] ? strftime('%x', $u['mkdate']) : '',
                        $u->online->last_lifesign ? strftime('%x', $u->online->last_lifesign) : ''
                    ];
                    foreach ($this->datafields as $datafield) {
                        $df = new DatafieldEntryModel(
                            [
                                $datafield->id,
                                $u['user_id'],
                                '',
                                ''
                            ]);
                        $data[] = $df->getTypedDatafield()->getDisplayValue(false);
                    }
                    return $data;
                };
                if (array_to_csv(array_map($mapper, $this->users), $GLOBALS['TMP_PATH'] . '/' . $tmpname, $captions)) {
                    $this->redirect(
                        FileManager::getDownloadURLForTemporaryFile(
                            $tmpname,
                            'nutzer-export.csv'
                        )
                    );
                }
            }
        }

        $this->degrees      = Abschluss::findBySQL('1 order by name');
        $this->studycourses = Fach::findBySQL('1 order by name');
        $this->userdomains  = UserDomain::getUserDomains();
        $this->institutes   = Institute::getInstitutes();
        foreach ($GLOBALS['STUDIP_AUTH_PLUGIN'] as $ap) {
            $this->available_auth_plugins[mb_strtolower($ap)] = $ap;
        }

        //show datafields search
        if ($advanced
            || !empty($search_datafields)
            || (!empty($request)
                && (!empty($request['auth_plugins']) || !empty($request['userdomains']) || !empty($request['degree']) ||
                    !empty($request['institute']) || !empty($request['studycourse']) || !empty($request['show_only_not_lectures']) || !empty($request['roles']))
            )
        ) {
            $this->advanced = true;
        }
    }

    /**
     * Bulk action (delete users or send message to all)
     */
    public function bulk_action($user_id = null)
    {
        $action = Request::option('method');

        if ($action === 'delete') {
            PageLayout::setTitle(_('Folgende Nutzer löschen'));
            if ($user_id) {
                $this->users = [User::find($user_id)];
            } else {
                $this->users = User::findMany(Request::getArray('user_ids'));
            }
            $this->render_template('admin/user/_delete.php');
            return;
        } elseif ($action === 'send_message') {
            $users = User::findMany(Request::getArray('user_ids'));

            if ($users) {
                $users = new SimpleCollection($users);
                $users = $users->pluck('username');
            }

            $_SESSION['sms_data']          = [];
            $_SESSION['sms_data']['p_rec'] = array_filter($users);
            $this->redirect(URLHelper::getURL('dispatch.php/messages/write', ['default_subject' => '', 'tmpsavesnd' => 1]));
            return;
        }
        $this->relocate('admin/user');
    }

    /**
     * Deleting one or more users
     *
     * @param string $user_id
     * @param string $parent redirect to this page after deleting users
     */
    public function delete_action($user_id = null, $parent = '')
    {
        $delete_documents           = Request::bool('documents', false);
        $delete_courseware          = Request::bool('courseware', false);
        $delete_content_from_course = Request::bool('coursecontent', false);
        $delete_personal_documents  = Request::bool('personaldocuments', false);
        $delete_personal_content    = Request::bool('personalcontent', false);
        $delete_names               = Request::bool('personalnames', false);
        $delete_memberships         = Request::bool('memberships', false);

        //deleting one user
        if (!is_null($user_id)) {
            $user = User::find($user_id);

            //check user
            if (!count($user)) {
                PageLayout::postError(_('Fehler! Zu löschende Person ist nicht vorhanden.'));
                //antwort ja
            } elseif (!empty($user) && Request::submitted('delete')) {
                CSRFProtection::verifyUnsafeRequest();

                //if deleting user, go back to mainpage
                $parent = '';

                //preparing delete
                $umanager = new UserManagement();
                $umanager->getFromDatabase($user_id);

                //delete
                if ($umanager->deleteUser($delete_documents, $delete_content_from_course, $delete_personal_documents, $delete_personal_content, $delete_names, $delete_memberships, !Request::bool('mail'), $delete_courseware)) {
                    $details = explode('§', str_replace(['msg§', 'info§', 'error§'], '', mb_substr($umanager->msg, 0, -1)));
                    PageLayout::postSuccess(htmlReady(sprintf(_('"%s (%s)" wurde erfolgreich gelöscht.'), $user->getFullName(), $user->username)), $details);
                } else {
                    $details = explode('§', str_replace(['msg§', 'info§', 'error§'], '', mb_substr($umanager->msg, 0, -1)));
                    PageLayout::postError(htmlReady(sprintf(_('Fehler! "%s (%s)" konnte nicht gelöscht werden.'), $user->getFullName(), $user->username)), $details);
                }

                //sicherheitsabfrage
            } elseif (!empty($user) && !Request::submitted('back')) {

                $this->flash['delete'] = [
                    'question' => sprintf(_('Wollen Sie "%s (%s)" wirklich löschen?'), $user->getFullName(), $user->username),
                    'action'   => ($parent != '') ? $this->url_for('admin/user/delete/' . $user_id . '/' . $parent) : $this->url_for('admin/user/delete/' . $user_id),
                ];
            }

            //deleting more users
        } else {
            $user_ids = Request::getArray('user_ids');

            if (count($user_ids) == 0) {
                PageLayout::postError(_('Bitte wählen Sie mindestens eine Person zum Löschen aus.'));
                $this->redirect('admin/user/' . $parent);
                return;
            }

            if (Request::submitted('delete')) {
                CSRFProtection::verifyUnsafeRequest();

                foreach ($user_ids as $i => $_user_id) {
                    $users[$i] = User::find($_user_id);
                    //preparing delete
                    $umanager = new UserManagement();
                    $umanager->getFromDatabase($_user_id);

                    //delete
                    if ($umanager->deleteUser($delete_documents, $delete_content_from_course, $delete_personal_documents, $delete_personal_content, $delete_names, $delete_memberships, !Request::bool('mail'))) {
                        $details = explode('§', str_replace(['msg§', 'info§', 'error§'], '', mb_substr($umanager->msg, 0, -1)));
                        PageLayout::postSuccess(htmlReady(sprintf(_('"%s (%s)" wurde erfolgreich gelöscht'), $users[$i]->getFullName(), $users[$i]->username)), $details);
                    } else {
                        $details = explode('§', str_replace(['msg§', 'info§', 'error§'], '', mb_substr($umanager->msg, 0, -1)));
                        PageLayout::postError(htmlReady(sprintf(_('Fehler! "%s (%s)" konnte nicht gelöscht werden'), $users[$i]->getFullName(), $users[$i]->username)), $details);
                    }
                }
            }
        }

        //liste wieder anzeigen
        if ($parent == 'edit') {
            $this->redirect('admin/user/edit/' . $user_id);
        } else {
            $this->redirect('admin/user/' . $parent);
        }
    }



    /**
     * Display all information according to the selected user. All details can
     * be changed and deleted.
     *
     * @param string $user_id
     */
    public function edit_action($user_id = null)
    {
        //check submitted user_id
        if ($user_id === null) {
            if (Request::option('user')) {
                $user_id = Request::option('user');
            }
        }

        $this->user = User::find($user_id);

        if (!$this->user) {
            PageLayout::postInfo(_('Sie haben niemanden ausgewählt!'));
            //liste wieder anzeigen
            $this->redirect('admin/user/');
            return;
        }

        $this->user_roles = $this->user->getRoles();

        // Änderungen speichern
        if (Request::submitted('edit')) {
            if (Request::get('auth_plugin') === 'preliminary') {
                Request::set('auth_plugin', null);
            }
            $editPerms = Request::getArray('perms');
            $um        = new UserManagement($user_id);

            //new user data
            $editUser = [];
            if (count($editPerms)) {
                $editUser['auth_user_md5.perms'] = $editPerms[0];
            }
            foreach (['Vorname', 'Nachname', 'matriculation_number', 'auth_plugin', 'visible'] as $param) {
                if (Request::get($param)) $editUser['auth_user_md5.' . $param] = Request::get($param);
            }
            foreach (words('title_front title_rear geschlecht preferred_language') as $param) {
                if (Request::get($param) !== null) $editUser['user_info.' . $param] = Request::get($param);
            }
            //change username
            if (Request::get('username') && $this->user['username'] !== Request::get('username')) {
                $editUser['auth_user_md5.username'] = Request::get('username');
            }
            //change email
            if (Request::get('Email') && $this->user['Email'] !== Request::get('Email')) {
                //disable mailbox validation
                if (Request::get('disable_mail_host_check')) {
                    $GLOBALS['MAIL_VALIDATE_BOX'] = false;
                }
                $editUser['auth_user_md5.Email'] = Request::get('Email');
            }

            //change password
            if (
                $GLOBALS['perm']->have_perm('root')
                && Config::get()->ALLOW_ADMIN_USERACCESS
                && !StudipAuthAbstract::CheckField('auth_user_md5.password', $this->user->auth_plugin)
                && $this->user->auth_plugin !== null
                && (Request::get('pass_1') !== '' || Request::get('pass_2') !== '')
            ) {
                if (Request::get('pass_1') === Request::get('pass_2')) {
                    $validator = new email_validation_class();
                    if (!$validator->ValidatePassword(Request::get('pass_1'))) {
                        $details[] = _('Das Passwort ist zu kurz. Es sollte mindestens 8 Zeichen lang sein.');
                    } else {
                        $um->changePassword(Request::get('pass_1'));
                    }
                } else {
                    $details[] = _("Bei der Wiederholung des Passwortes ist ein Fehler aufgetreten! Bitte geben Sie das exakte Passwort ein!");
                }
            }

            //deleting validation-key
            if (Request::get('delete_val_key') == "1") {
                $editUser['auth_user_md5.validation_key'] = '';
                $details[]                                = _('Der Validation-Key wurde entfernt.');
            }

            //changing studiendaten
            if (in_array($editPerms[0], ['autor', 'tutor', 'dozent']) && Request::option('new_studiengang', 'none') != 'none' && Request::option('new_abschluss', 'none') != 'none') {
                //change studycourses
                if (Request::option('new_studiengang', 'none') == 'none' || Request::option('new_abschluss', 'none') == 'none') {
                    $details[] = _('<b>Der Studiengang wurde nicht hinzugefügt.</b> Bitte geben Sie Fach und Abschluss ein.');
                } else {
                    $user_stc = UserStudyCourse::find([
                        $user_id,
                        Request::option('new_studiengang'),
                        Request::option('new_abschluss'),
                    ]);
                    if (!$user_stc) {
                        UserStudyCourse::create([
                            'user_id'      => $user_id,
                            'fach_id'      => Request::option('new_studiengang'),
                            'semester'     => Request::int('fachsem'),
                            'abschluss_id' => Request::option('new_abschluss'),
                        ]);
                        $details[] = _('Der Studiengang wurde hinzugefügt.');
                    } else {
                        $user_stc->semester = Request::int('fachsem');
                        if ($user_stc->store()) {
                            $details[] = _('Der Studiengang wurde geändert.');
                        } else {
                            $details[] = _('Der Studiengang wurde nicht geändert.');
                        }
                    }
                }
            }

            // change version of studiengang if module management is enabled
            if (in_array($editPerms[0], ['autor', 'tutor', 'dozent'])) {
                $change_versions = Request::getArray('change_version');
                $any_change = false;
                foreach ($change_versions as $fach_id => $abschluesse) {
                    foreach ($abschluesse as $abschluss_id => $version_id) {
                        $version = StgteilVersion::findByFachAbschluss(
                            $fach_id, $abschluss_id, $version_id
                        );
                        $version = reset($version);
                        if ($version && $version->hasPublicStatus('genehmigt')) {
                            $user_stc = UserStudyCourse::find([
                                $user_id,
                                $fach_id,
                                $abschluss_id]);
                            if ($user_stc) {
                                $user_stc->version_id = $version->getId();
                                $any_change           = $user_stc->store() != false;
                            }
                        }
                    }
                }
                if ($any_change) {
                    $details[] = _('Die Versionen der Studiengänge wurden geändert.');
                }
            }
            $new_institutes = Request::getArray('new_inst');

            //change institute for studiendaten
            if (in_array($editPerms[0], ['autor', 'tutor', 'dozent'])
                && Request::option('new_student_inst')
                && empty($new_institutes)
                && $GLOBALS['perm']->have_studip_perm("admin", Request::option('new_student_inst'))
            ) {
                StudipLog::log('INST_USER_ADD', Request::option('new_student_inst'), $user_id, 'user');
                $db = DBManager::get()->prepare("INSERT IGNORE INTO user_inst (user_id, Institut_id, inst_perms) "
                    . "VALUES (?,?,'user')");
                $db->execute([$user_id, Request::option('new_student_inst')]);
                NotificationCenter::postNotification('UserInstitutionDidCreate', Request::option('new_student_inst'), $user_id);
                $details[] = _('Die Einrichtung wurde hinzugefügt.');
            }

            //change institute
            if (!empty($new_institutes)) {
                foreach ($new_institutes as $institute_id) {
                    if ($editPerms[0] != 'root'
                        && $GLOBALS['perm']->have_studip_perm("admin", $institute_id)
                        && !Request::option('new_student_inst')
                    ) {
                        $membership = InstituteMember::build(
                            ['user_id' => $user_id, 'Institut_id' => $institute_id, 'inst_perms' => $editPerms[0]]
                        );

                        if ($membership->store()) {
                            StudipLog::log('INST_USER_ADD', $institute_id, $user_id, $editPerms[0]);
                            NotificationCenter::postNotification('UserInstitutionDidUpdate', $institute_id, $user_id);
                            InstituteMember::ensureDefaultInstituteForUser($user_id);
                            $details[] = sprintf(_('%s wurde hinzugefügt.'), htmlReady($membership->institute->getFullname()));
                        }
                    } elseif ($institute_id != '' && Request::option('new_student_inst') == $institute_id && $editPerms[0] != 'root') {
                        $details[] = sprintf(
                            _('<b>%s wurde nicht hinzugefügt.</b> Sie können keine Person gleichzeitig als Studierende/-r und als Mitarbeiter/-in einer Einrichtung hinzufügen.'),
                            htmlReady(Institute::find($institute_id)->getFullname())
                        );
                    }
                }
            }

            //change userdomain
            if (Request::get('new_userdomain', 'none') != 'none' && $editPerms[0] != 'root') {
                UserDomain::find(Request::get('new_userdomain'))->addUser($user_id);
                $result = AutoInsert::instance()->saveUser($user_id);

                $details[] = _('Die Nutzerdomäne wurde hinzugefügt.');
                foreach ($result['added'] as $item) {
                    $details[] = sprintf(_("Das automatische Eintragen in die Veranstaltung <em>%s</em> wurde durchgeführt."), $item);
                }
                foreach ($result['removed'] as $item) {
                    $details[] = sprintf(_("Das automatische Austragen aus der Veranstaltung <em>%s</em> wurde durchgeführt."), $item);
                }
            }

            //change datafields
            $datafields = Request::getArray('datafields');
            foreach (DataFieldEntry::getDataFieldEntries($user_id) as $id => $entry) {
                if (isset($datafields[$id])) {
                    $entry->setValueFromSubmit($datafields[$id]);
                    if ($entry->isValid()) {
                        $entry->store();
                    }
                }
            }

            //change ablaufdatum
            if (Request::get('expiration_date_delete') == 1) {
                UserConfig::get($user_id)->delete("EXPIRATION_DATE");
            } elseif (Request::get('expiration_date')) {
                $a = explode(".", stripslashes(trim(Request::get('expiration_date'))));
                if ($timestamp = @mktime(0, 0, 0, $a[1], $a[0], $a[2])) {
                    UserConfig::get($user_id)->store("EXPIRATION_DATE", $timestamp);
                    $details[] = _("Das Ablaufdatum wurde geändert.");
                } else {
                    $details[] = _("Das Ablaufdatum wurde in einem falschen Format angegeben.");
                }
            }

            if ($GLOBALS['perm']->have_perm('root') && Request::get('lock_rule')) {
                $st = DBManager::get()->prepare("UPDATE user_info SET lock_rule=? WHERE user_id=?");
                $st->execute([(Request::option('lock_rule') == 'none' ? '' : Request::option('lock_rule')), $user_id]);
                if ($st->rowCount()) {
                    $details[] = _("Die Sperrebene wurde geändert.");
                }
            }

            if (!Request::int('u_edit_send_mail')) {
                $dev_null       = new blackhole_message_class();
                $default_mailer = StudipMail::getDefaultTransporter();
                StudipMail::setDefaultTransporter($dev_null);
                $GLOBALS['MAIL_VALIDATE_BOX']  = false;
                $GLOBALS['MAIL_VALIDATE_HOST'] = false;
            }
            //save action and messages
            $um->changeUser($editUser);
            if (!Request::int('u_edit_send_mail') && isset($default_mailer)) {
                StudipMail::setDefaultTransporter($default_mailer);
            }
            //get message
            $umdetails = explode('§', str_replace(['msg§', 'info§', 'error§'], '', mb_substr($um->msg, 0, -1)));
            if (!empty($details)) {
                $details   = array_reverse(array_merge((array)$details, (array)$umdetails));
                PageLayout::postInfo(_('Hinweise:'), $details);
            }

            $this->redirect('admin/user/edit/' . $user_id);
        }

        $this->prelim = $this->user->auth_plugin === null;
        if ($this->prelim) {
            $this->available_auth_plugins['preliminary'] = _('vorläufig');
        }
        foreach ($GLOBALS['STUDIP_AUTH_PLUGIN'] as $ap) {
            $this->available_auth_plugins[mb_strtolower($ap)] = $ap;
        }

        if (!empty($this->user->institute_memberships)) {
            $this->student_institutes = $this->user->institute_memberships->filter(function ($a) {
                return $a->inst_perms === 'user';
            });
            $this->institutes = $this->user->institute_memberships->filter(function ($a) {
                return $a->inst_perms !== 'user';
            });
        }

        $this->available_institutes = Institute::getMyInstitutes();
        $this->userfields           = DataFieldEntry::getDataFieldEntries($user_id, 'user');
        $this->userdomains          = UserDomain::getUserDomainsForUser($user_id);
        if (LockRules::CheckLockRulePermission($user_id) && LockRules::getObjectRule($user_id)->description) {
            PageLayout::postInfo(formatLinks(LockRules::getObjectRule($user_id)->description));
        }

        $user_domains      = UserDomain::getUserDomainsForUser($this->user->user_id);
        $all_domains       = UserDomain::getUserDomains();
        $this->domains     = array_diff($all_domains, $user_domains);
        $this->faecher     = Fach::findBySQL('1 ORDER BY name');
        $this->abschluesse = Abschluss::findBySQL('1 ORDER BY name');
    }

    /**
     * Adding a new user to Stud.IP
     * @param bool $prelim
     */
    public function new_action($prelim = false)
    {
        $this->perm   = $GLOBALS['perm'];
        $this->prelim = $prelim;

        //check auth_plugins
        if (!in_array("Standard", $GLOBALS['STUDIP_AUTH_PLUGIN']) && !$prelim) {
            PageLayout::postInfo(_('Die Standard-Authentifizierung ist ausgeschaltet. Das Anlegen von neuen Benutzern ist nicht möglich!'));
            $this->redirect('admin/user');
            return;
        }

        //get formdata
        $this->user = [
            'username'    => trim(Request::get('username')),
            'perm'        => Request::option('perm'),
            'visible'     => Request::get('visible'),
            'Vorname'     => trim(Request::get('Vorname')),
            'Nachname'    => trim(Request::get('Nachname')),
            'geschlecht'  => Request::int('geschlecht'),
            'title_front' => trim(Request::get('title_front')),
            'title_rear'  => trim(Request::get('title_rear')),
            'Email'       => trim(Request::get('Email')),
            'auth_plugin' => Request::get('auth_plugin'),
            'institute'   => Request::option('institute'),
            'preferred_language' => Request::get('preferred_language')
            ];

        //save new user
        if (Request::submitted('speichern')) {

            //disable mailbox validation
            if (Request::get('disable_mail_host_check')) {
                $GLOBALS['MAIL_VALIDATE_BOX'] = false;
            }

            //messagebox details
            $details = [];

            //new user data
            $newuser = [
                'auth_user_md5.username'    => $this->user['username'],
                'auth_user_md5.Vorname'     => $this->user['Vorname'],
                'auth_user_md5.Nachname'    => $this->user['Nachname'],
                'auth_user_md5.Email'       => $this->user['Email'],
                'auth_user_md5.perms'       => $this->user['perm'],
                'auth_user_md5.auth_plugin' => $this->user['auth_plugin'],
                'auth_user_md5.visible'     => $this->user['visible'],
                'user_info.title_front'     => $this->user['title_front'],
                'user_info.title_rear'      => $this->user['title_rear'],
                'user_info.geschlecht'      => $this->user['geschlecht'],
                'user_info.preferred_language' => $this->user['preferred_language'],
            ];

            //create new user
            $UserManagement = new UserManagement();
            if (!$prelim) {
                $created = $UserManagement->createNewUser($newuser);
            } else {
                $created = $UserManagement->createPreliminaryUser($newuser);
            }
            if ($created) {

                //get user_id
                $user_id = $UserManagement->user_data['auth_user_md5.user_id'];
                $institutes = Request::getArray('institutes');

                if (!empty($institutes)) {
                    $institutes = Institute::findMany($institutes);
                    foreach ($institutes as $institute) {
                        //new user is added to an institute
                        if ($GLOBALS['perm']->have_studip_perm('admin', $institute->id)
                            && $UserManagement->user_data['auth_user_md5.perms'] != 'root'
                            && ($UserManagement->user_data['auth_user_md5.perms'] != 'admin'
                                || ($GLOBALS['perm']->is_fak_admin() && !$institute->isFaculty())
                                || $GLOBALS['perm']->have_perm('root'))
                        ) {
                            //log
                            StudipLog::log(
                                'INST_USER_ADD',
                                $institute->id,
                                $user_id,
                                $UserManagement->user_data['auth_user_md5.perms']);

                            $inst_user = InstituteMember::build([
                                'user_id' => $user_id,
                                'Institut_id' => $institute->id,
                                'inst_perms' => $UserManagement->user_data['auth_user_md5.perms']
                            ])->store();
                            NotificationCenter::postNotification('UserInstitutionDidCreate', $institute->id, $user_id);
                            InstituteMember::ensureDefaultInstituteForUser($user_id);

                            //send email, if new user is an admin
                            if ($inst_user) {
                                //check recipients
                                if (Request::get('enable_mail_admin') === 'admin' && Request::get('enable_mail_dozent') === 'dozent') {
                                    $in  = words('admin dozent');
                                    $wem = "Admins und Lehrende";
                                } elseif (Request::get('enable_mail_admin') === 'admin') {
                                    $in  = 'admin';
                                    $wem = "Admins";
                                } elseif (Request::get('enable_mail_dozent') === 'dozent') {
                                    $in  = 'dozent';
                                    $wem = "Lehrende";
                                }

                                if (!empty($in) && Request::get('perm') == 'admin') {
                                    $i     = 0;
                                    $notin = [];

                                    //get admins
                                    $sql = "SELECT user_id, b.Vorname, b.Nachname, b.Email
                                    FROM user_inst AS a
                                    INNER JOIN auth_user_md5 AS b USING (user_id)
                                    WHERE a.Institut_id = ? AND a.inst_perms IN (?) AND a.user_id != ?";

                                    $statement = DBManager::get()->prepare($sql);
                                    $statement->execute([
                                        $institute->id,
                                        $in,
                                        $user_id,
                                    ]);
                                    $users = $statement->fetchAll(PDO::FETCH_ASSOC);

                                    foreach ($users as $admin) {
                                        $subject  = _("Neuer Administrator in Ihrer Einrichtung angelegt");
                                        $mailbody = sprintf(_("Liebe(r) %s %s,\n\n"
                                            . "in der Einrichtung '%s' wurde %s %s als Administrator eingetragen "
                                            . " und steht Ihnen als neuer Ansprechpartner bei Fragen oder Problemen "
                                            . "in Stud.IP zur Verfügung. "),
                                            $admin['Vorname'], $admin['Nachname'],
                                            $institute->getFullname(), $this->user['Vorname'], $this->user['Nachname']);

                                        StudipMail::sendMessage($admin['Email'], $subject, $mailbody);
                                        $notin[] = $admin['user_id'];
                                        $i++;
                                    }

                                    //Noch ein paar Mails für die Fakultätsadmins
                                    if ($in != 'dozent') {
                                        $notin[] = $user_id;
                                        //get admins
                                        $sql = "SELECT a.user_id, b.Vorname, b.Nachname, b.Email
                                        FROM user_inst AS a
                                        INNER JOIN auth_user_md5 AS b USING (user_id)
                                        WHERE a.user_id NOT IN (?) AND a.Institut_id IN (
                                            SELECT fakultaets_id
                                            FROM Institute
                                            WHERE Institut_id = ? AND fakultaets_id != Institut_id
                                        ) AND a.inst_perms = 'admin'";
                                        $statement = DBManager::get()->prepare($sql);
                                        $statement->execute([
                                            $notin,
                                            $institute->id,
                                        ]);
                                        $fak_admins = $statement->fetchAll(PDO::FETCH_ASSOC);

                                        foreach ($fak_admins as $admin) {
                                            $subject  = _("Neuer Administrator in Ihrer Einrichtung angelegt");
                                            $mailbody = sprintf(_("Liebe(r) %s %s,\n\n"
                                                . "in der Einrichtung '%s' wurde %s %s als Administrator eingetragen "
                                                . " und steht Ihnen als neuer Ansprechpartner bei Fragen oder Problemen "
                                                . "in Stud.IP zur Verfügung. "),
                                                $admin['Vorname'], $admin['Nachname'],
                                                $institute->getFullname(), $this->user['Vorname'], $this->user['Nachname']);

                                            StudipMail::sendMessage($admin['Email'], $subject, $mailbody);
                                            $i++;
                                        }
                                    }

                                    if ($i >0 && isset($wem)) {
                                        $details[] = sprintf(
                                            _('Es wurden ingesamt %s Mails an die %s der Einrichtung "%s" geschickt.'),
                                            $i,
                                            $wem,
                                            htmlReady($institute->getFullname())
                                        );
                                    }
                                }

                                $details[] = sprintf(
                                    _('Person wurde erfolgreich in die Einrichtung "%s" mit dem Status "%s" eingetragen.'),
                                    htmlReady($institute->getFullname()),
                                    $UserManagement->user_data['auth_user_md5.perms']
                                );
                            } else {
                                $details[] = sprintf(
                                    _('Person konnte nicht in die Einrichtung "%s" eingetragen werden.'),
                                    htmlReady($institute->getFullname())
                                );
                            }
                        }
                    }
                }

                //adding userdomain
                if (Request::get('select_dom_id')) {
                    $domain = new UserDomain(Request::get('select_dom_id'));
                    if ($GLOBALS['perm']->have_perm('root') || in_array($domain, UserDomain::getUserDomainsForUser($GLOBALS['user']->id))) {
                        $domain->addUser($user_id);
                        $details[] = sprintf(_('Person wurde in Nutzerdomäne "%s" eingetragen.'), htmlReady($domain->name));
                    } else {
                        $details[] = _('Person konnte nicht in die Nutzerdomäne eingetragen werden.');
                    }
                    $result = AutoInsert::instance()->saveUser($user_id);

                    foreach ($result['added'] as $item) {
                        $details[] = sprintf(_('Das automatische Eintragen in die Veranstaltung <em>%s</em> wurde durchgeführt.'), $item);
                    }
                    foreach ($result['removed'] as $item) {
                        $details[] = sprintf(_('Das automatische Austragen aus der Veranstaltung <em>%s</em> wurde durchgeführt.'), $item);
                    }
                }

                //get message
                $details = explode('§', str_replace(['msg§', 'info§', 'error§'], '', mb_substr($UserManagement->msg, 0, -1)));
                PageLayout::postSuccess(_('Person wurde angelegt.'), $details);
                $this->redirect('admin/user/edit/' . $user_id);
                return;
            } else {
                //get message
                $details = explode('§', str_replace(['msg§', 'info§', 'error§'], '', mb_substr($UserManagement->msg, 0, -1)));
                PageLayout::postError(_('Person konnte nicht angelegt werden.'), $details);
            }
        }

        if ($GLOBALS['perm']->have_perm('root')) {
            $sql = "SELECT Institut_id, Name, 1 AS is_fak
                    FROM Institute
                    WHERE Institut_id=fakultaets_id
                    ORDER BY Name";
            $faks    = DBManager::get()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            $domains = UserDomain::getUserDomains();
        } else {
            $sql = "SELECT a.Institut_id, Name, b.Institut_id = b.fakultaets_id AS is_fak
                    FROM user_inst a
                    LEFT JOIN Institute b USING (Institut_id)
                    WHERE a.user_id = ? AND a.inst_perms = 'admin'
                    ORDER BY is_fak, Name";
            $statement = DBManager::get()->prepare($sql);
            $statement->execute([User::findCurrent()->id]);
            $faks    = $statement->fetchAll(PDO::FETCH_ASSOC);
            $domains = UserDomain::getUserDomainsForUser(User::findCurrent()->id);
        }

        $query = "SELECT Institut_id, Name
                  FROM Institute
                  WHERE fakultaets_id = ? AND institut_id != fakultaets_id
                  ORDER BY Name";
        $statement = DBManager::get()->prepare($query);

        foreach ($faks as $index => $fak) {
            if ($fak['is_fak']) {
                $statement->execute([$fak['Institut_id']]);
                $faks[$index]['institutes'] = $statement->fetchAll(PDO::FETCH_ASSOC);
                $statement->closeCursor();
            }
        }

        $this->domains = $domains;
        $this->faks    = $faks;
        $this->perms   = $GLOBALS['perm'];
    }

    /**
     * Migrate 2 users to 1 account. This is a part of the old numit-plugin
     */
    public function migrate_action($user_id = null)
    {
        //check submitted form
        if (Request::submitted('umwandeln')) {
            $old_id = Request::option('old_id');
            $new_id = Request::option('new_id');

            //check existing users
            if (User::exists($old_id) && User::exists($new_id)) {
                $identity = Request:: get('convert_ident');
                $details  = User::convert($old_id, $new_id, $identity);

                //delete old user
                if (Request::get('delete_old')) {
                    //preparing delete
                    $umanager = new UserManagement();
                    $umanager->getFromDatabase($old_id);

                    //delete
                    $umanager->deleteUser(
                        true,
                        true,
                        true,
                        true,
                        true,
                        true,
                        false
                    );
                    $details = array_merge($details, explode('§', str_replace(['msg§', 'info§', 'error§'], '', mb_substr($umanager->msg, 0, -1))));
                }

                PageLayout::postSuccess(_('Die Personen wurden migriert.'), $details);
                $this->redirect('admin/user/edit/' . $new_id);
            } else {
                PageLayout::postError(_('Bitte wählen Sie zwei gültige Personen aus.'));
            }
        }
        $this->user = $user_id ? User::find($user_id) : null;
    }

    /**
     * Set the password of an user to a new random password, without security-query
     *
     * @param string $user_id
     */
    public function change_password_action($user_id)
    {
        CSRFProtection::verifyUnsafeRequest();
        // mail address did not change, so skip this check
        $GLOBALS['MAIL_VALIDATE_BOX'] = false;
        $UserManagement               = new UserManagement($user_id);
        if ($UserManagement->setPassword()) {
            PageLayout::postSuccess(_('Es wurde eine Mail mit einem Link zum Neusetzen '
                . 'des Passworts verschickt, das bestehende Passwort wurde nicht verändert.'));
        } else {
            $details = explode('§', str_replace(['msg§', 'info§', 'error§'], '', mb_substr($UserManagement->msg, 0, -1)));
            PageLayout::postError(_('Die Änderungen konnten nicht gespeichert werden.'), $details);
        }
        if (Request::int('from_index')) {
            $this->relocate('admin/user');
        } else {
            $this->redirect('admin/user/edit/' . $user_id);
        }
    }

    /**
     * Add lock-comment for locked user
     * @param $user_id
     */
    public function lock_comment_action($user_id)
    {
        $this->user = User::find($user_id);
        PageLayout::setTitle(sprintf(_('%s sperren'), $this->user->getFullname()));

        $this->params = [];
        if (Request::int('from_index')) {
            $this->params['from_index'] = 1;
        }
    }

    /**
     * Lock user
     * @param $user_id
     */
    public function lock_action($user_id)
    {
        CSRFProtection::verifyUnsafeRequest();
        $user = User::find($user_id);

        $user->locked       = 1;
        $user->lock_comment = Request::get('lock_comment');
        $user->locked_by    = $GLOBALS['user']->id;

        if ($user->store()) {
            PageLayout::postSuccess(sprintf(
                _('%s wurde gesperrt.'),
                htmlReady($user->getFullname())
            ));
        }

        if (Request::int('from_index')) {
            $this->relocate('admin/user');
        } else {
            $this->redirect('admin/user/edit/' . $user_id);
        }
    }

    /**
     * Unlock an user, without security-query
     *
     * @param string $user_id
     */
    public function unlock_action($user_id)
    {
        CSRFProtection::verifyUnsafeRequest();
        $user = User::find($user_id);

        $user->locked       = 0;
        $user->lock_comment = null;
        $user->locked_by    = null;

        if ($user->store()) {
            PageLayout::postSuccess(sprintf(
                _('%s wurde entsperrt.'),
                htmlReady($user->getFullname())
            ));
        } else {
            PageLayout::postError(sprintf(
                _('%s konnte nicht entsperrt werden.'),
                htmlReady($user->getFullname())
            ));
        }

        if (Request::int('from_index')) {
            $this->relocate('admin/user');
        } else {
            $this->redirect('admin/user/edit/' . $user_id);
        }
    }


    /**
     * Display institute informations of an user and save changes to it.
     *
     * @param string $user_id
     * @param string $institute_id
     */
    public function edit_institute_action($user_id, $institute_id)
    {
        $this->user = User::find($user_id);
        $institute = null;
        if (!empty($this->user->institute_memberships)) {
            $this->user->institute_memberships->filter(function ($a) use ($institute_id, &$institute) {
                if ($a->institut_id === $institute_id) {
                    $institute = $a;
                }
            });
        }

        $this->institute   = $institute;
        $this->faecher     = StudyCourse::findBySQL('1 ORDER BY name');
        $this->abschluesse = Abschluss::findBySQL('1 ORDER by name');
        $this->perms       = $this->user->getInstitutePerms();
        $this->datafields  = DataFieldEntry::getDataFieldEntries([$user_id, $institute_id], 'userinstrole');
    }

    /**
     * Set user institute information
     * @param string $user_id
     * @param string $institute_id
     */
    public function store_user_institute_action($user_id, $institute_id)
    {
        CSRFProtection::verifyRequest();

        $inst_membership = InstituteMember::findOneBySQL('user_id = ? AND institut_id = ?', [$user_id, $institute_id]);

        //change datafields
        $datafields = Request::getArray('datafields');
        foreach ($datafields as $id => $data) {
            $datafield = DataField::find($id);
            $entry = DataFieldEntry::createDataFieldEntry($datafield, [$user_id, $institute_id]);
            $entry->setValueFromSubmit($data);
            if ($entry->isValid()) {
                $entry->store();
            }
        }

        if ($inst_membership->inst_perms != Request::get('inst_perms')) {
            StudipLog::log('INST_USER_STATUS', $institute_id, $user_id, $inst_membership->inst_perms . ' -> ' . Request::get('inst_perms'));
            NotificationCenter::postNotification('UserInstitutionPermDidUpdate', $institute_id, $user_id);
        }

        $inst_membership->inst_perms    = Request::get('inst_perms', '');
        $inst_membership->visible       = Request::int('visible', 0);
        $inst_membership->sprechzeiten  = Request::get('sprechzeiten', '');
        $inst_membership->telefon       = Request::get('telefon', '');
        $inst_membership->fax           = Request::get('fax', '');
        $inst_membership->externdefault = Request::int('externdefault', 0);
        $inst_membership->raum          = Request::get('raum', '');
        $inst_membership->store();

        //output
        PageLayout::postSuccess(_('Die Einrichtungsdaten der Person wurden geändert.'));
        $this->relocate('admin/user/edit/' . $user_id);
        return;
    }

    /**
     * Delete an studycourse of an user , without a security-query
     *
     * @param string $user_id
     * @param string $fach_id
     * @param string $abschluss_id
     */
    public function delete_studycourse_action($user_id, $fach_id, $abschlus_id)
    {
        CSRFProtection::verifyUnsafeRequest();
        $user_stc = UserStudyCourse::find([$user_id, $fach_id, $abschlus_id]);
        $deleted  = false;
        if ($user_stc) {
            $deleted = $user_stc->delete();
        }
        if ($deleted) {
            PageLayout::postSuccess(_('Die Zuordnung zum Studiengang wurde gelöscht.'));
        } else {
            PageLayout::postError(_('Die Zuordnung zum Studiengang konnte nicht gelöscht werden.'));
        }
        $this->redirect('admin/user/edit/' . $user_id);
    }

    /**
     * Delete an institute of an user , without a security-query
     *
     * @param string $user_id
     * @param string $institut_id
     */
    public function delete_institute_action($user_id, $institut_id)
    {
        CSRFProtection::verifyUnsafeRequest();
        if ($GLOBALS['perm']->have_studip_perm("admin", $institut_id)) {
            $groups     = GetAllStatusgruppen($institut_id);
            $group_list = GetRoleNames($groups, 0, '', true);
            if (is_array($group_list) && count($group_list) > 0) {
                StatusgruppeUser::deleteBySQL(
                    "`statusgruppe_id` IN (?) AND `user_id` = ?",
                    [array_keys($group_list), $user_id]
                );
            }

            $count = InstituteMember::deleteBySQL("`user_id` = ? AND `Institut_id` = ?", [$user_id, $institut_id]);
            if ($count === 1) {
                StudipLog::log('INST_USER_DEL', $institut_id, $user_id);
                NotificationCenter::postNotification('UserInstitutionDidDelete', $institut_id, $user_id);
                InstituteMember::ensureDefaultInstituteForUser($user_id);
                if (UserConfig::get($user_id)->MY_INSTITUTES_DEFAULT == $institut_id) {
                    UserConfig::get($user_id)->delete('MY_INSTITUTES_DEFAULT');
                }
                PageLayout::postSuccess(_('Die Zuordnung zur Einrichtung wurde gelöscht.'));
            } else {
                PageLayout::postError(_('Die Zuordnung zur Einrichtung konnte nicht gelöscht werden.'));
            }
        } else {
            PageLayout::postError(_('Die Zuordnung zur Einrichtung konnte nicht gelöscht werden.'));
        }
        $this->redirect('admin/user/edit/' . $user_id);
    }

    /**
     * Delete an assignment of an user to an userdomain, without a security-query
     *
     * @param string $user_id
     */
    public function delete_userdomain_action($user_id)
    {
        CSRFProtection::verifyUnsafeRequest();
        $domain_id = Request::get('domain_id');
        UserDomain::find($domain_id)->removeUser($user_id);
        $result = AutoInsert::instance()->saveUser($user_id);

        $details = [];

        foreach ($result['added'] as $item) {
            $details[] = sprintf(_('Das automatische Eintragen in die Veranstaltung <em>%s</em> wurde durchgeführt.'), $item);
        }
        foreach ($result['removed'] as $item) {
            $details[] = sprintf(_('Das automatische Austragen aus der Veranstaltung <em>%s</em> wurde durchgeführt.'), $item);
        }

        PageLayout::postSuccess(_('Die Zuordnung zur Nutzerdomäne wurde erfolgreich gelöscht.'), $details);
        $this->redirect('admin/user/edit/' . $user_id);
    }

    /**
     * Reset notification for user
     * @param $user_id
     */
    public function reset_notification_action($user_id)
    {
        CSRFProtection::verifyUnsafeRequest();
        $resetted = CourseMemberNotification::deleteBySQL("user_id = ?", [$user_id]);
        PageLayout::postSuccess(sprintf(_('Die Benachrichtigungseinstellungen für %s Veranstaltungen wurden zurück gesetzt.'), $resetted));
        $this->redirect('admin/user/edit/' . $user_id);
    }

    /**
     * Reset two factor authentication for user
     * @param $user_id
     */
    public function reset_tfa_action($user_id)
    {
        CSRFProtection::verifyUnsafeRequest();
        if (TFASecret::deleteByUser_id($user_id)) {
            PageLayout::postSuccess(_('Die Zwei-Faktor-Authentifizierung wurde für diese Person deaktiviert.'));
        }
        $this->redirect('admin/user/edit/' . $user_id);
    }

    /**
     * Show user activities
     * @param $user_id
     * @throws Exception
     */
    public function activities_action($user_id)
    {
        $this->user     = User::find($user_id);
        $this->fullname = $this->user->getFullname();
        $this->params   = [];

        if (Request::int('from_index')) {
            $this->params['from_index'] = 1;
        }
        if (is_null($this->user)) {
            throw new Exception(_('Nutzer nicht gefunden'));
        }
        PageLayout::setTitle(sprintf(_('Datei- und Aktivitätsübersicht für %s'), $this->fullname));

        $this->queries = $this->getActivities($user_id);

        $memberships = DBManager::get()->fetchAll("SELECT seminar_user.*, seminare.Name as course_name
                             FROM seminar_user
                             LEFT JOIN seminare USING (seminar_id)
                             LEFT JOIN semester_courses ON (seminare.Seminar_id = semester_courses.course_id)
                             LEFT JOIN semester_data ON (semester_data.semester_id = semester_courses.semester_id)
                             WHERE user_id = ? GROUP BY seminare.Seminar_id ORDER BY MAX(semester_data.beginn) DESC, seminare.Name",
            [$user_id],
            'CourseMember::buildExisting');

        $courses        = [];
        $course_files   = [];
        $closed_courses = [];
        $this->sections = [];

        foreach ($memberships as $membership) {
            $semester_name = $membership->course->isOpenEnded() ? _('unbegrenzt') : (string)$membership->course->start_semester->name;
            if (!Request::get('view') || Request::get('view') === 'files') {
                // count files for course

                $top_folder = Folder::findTopFolder($membership->seminar_id);
                $top_folder = $top_folder->getTypedFolder();
                $count = FileManager::countFilesInFolder($top_folder, true, $user_id);


                if ($count) {
                    if (!isset($course_files[$semester_name])) {
                        $course_files[$semester_name] = [];
                    }
                    if (!isset($course_files[$semester_name][$membership->seminar_id])) {
                        $course_files[$semester_name][$membership->seminar_id] = [
                            'course' => $membership->course,
                        ];
                    }
                    $course_files[$semester_name][$membership->seminar_id]['files'] = $count;
                }
            }
            if (in_array(Request::get('view'), words('courses closed_courses'))) {
                // check for closed courses
                $closed_course = DBManager::get()->fetchColumn('SELECT COUNT(sc.seminar_id) FROM seminar_courseset sc
                  INNER JOIN courseset_rule cr ON cr.set_id=sc.set_id AND cr.type="ParticipantRestrictedAdmission"
                  WHERE sc.seminar_id =?', [$membership->seminar_id]);

                if ((int)$closed_course) {
                    $closed_courses[$semester_name][$membership->course->id] = $membership;
                } else {
                    $courses[$semester_name][$membership->course->id] = $membership;
                }
            }
        }

        if (!Request::get('view') || Request::get('view') === 'files') {
            $institutes = Institute::getMyInstitutes($user_id);
            if (!empty($institutes)) {
                foreach ($institutes as $index => $institute) {
                    $top_folder = Folder::findTopFolder($institute['Institut_id']);

                    $top_folder = $top_folder->getTypedFolder();

                    $count = FileManager::countFilesInFolder($top_folder, true, $user_id);

                    if ($count) {
                        $institutes[$index]['files'] = $count;
                    } else {
                        unset($institutes[$index]);
                    }
                }
            }
        }

        if (Request::get('view') === 'seminar_wait') {
            // waiting list
            $seminar_wait = AdmissionApplication::findByUser($user_id);
        } elseif (Request::get('view') === 'priorities') {
            // priorities
            $priorities = DBManager::get()->fetchAll('SELECT * FROM `priorities` WHERE `user_id` = ?', [$user_id]);
            $seminar_wait = [];
        }

        if (!empty($course_files)) {
            $this->sections['course_files'] = $course_files;
        }
        if (!empty($institutes)) {
            $this->sections['institutes'] = $institutes;
        }
        if (!empty($courses)) {
            $this->sections['courses'] = $courses;
        }
        if (!empty($closed_courses)) {
            $this->sections['closed_courses'] = $closed_courses;
        }

        if (!empty($seminar_wait)) {
            $this->sections['seminar_wait'] = $seminar_wait;
        }

        if (!empty($priorities)) {
            $this->sections['priorities'] = $priorities;
        }
    }

    /**
     * List files for course or institute
     * @param string $user_id
     * @param string $course_id
     */
    public function list_files_action($user_id, $range_id)
    {
        $this->user  = User::find($user_id);
        $folder = Folder::findTopFolder($range_id);
        if($folder) {
            $folder = $folder->getTypedFolder();
        }

        if($folder) {
            //Folder exists: We can collect all subfolders in the folder.
            $this->folders = FileManager::getFolderFilesRecursive($folder, $this->user->id)['folders'];
        } else {
            //Folder does not exist: We can't collect any subfolders.
            $this->folders = [];
        }

        $this->range = Course::find($range_id);
        if (is_null($this->range)) {
            $this->range = Institute::find($range_id);
        }
        PageLayout::setTitle(sprintf(_('Dateiübersicht für %s'), $this->range->getFullname()));
    }

    /**
     * Create array
     * @param string $user_id
     * @return array
     */
    private function getActivities($user_id)
    {
        $queries[] = [
            'desc'    => _('Eingetragen in Veranstaltungen (dozent / tutor / autor / user)'),
            'query'   => "SELECT CONCAT_WS(' / ', SUM(status = 'dozent'), SUM(status = 'tutor'),
                                          SUM(status = 'autor'), SUM(status = 'user'))
                  FROM seminar_user
                  WHERE user_id = ?
                  GROUP BY user_id",
            'details' => "courses",
        ];
        $queries[] = [
            'desc'    => _('Eingetragen in geschlossenen Veranstaltungen (dozent / tutor / autor / user)'),
            'query'   => "SELECT CONCAT_WS(' / ', SUM(su.status = 'dozent'), SUM(su.status = 'tutor'),
                                          SUM(su.status = 'autor'), SUM(su.status = 'user'))
                  FROM seminar_user AS su
                  INNER JOIN seminar_courseset sc USING (seminar_id)
                  INNER JOIN courseset_rule cr ON cr.set_id=sc.set_id AND cr.type='ParticipantRestrictedAdmission'
                  WHERE user_id = ?
                  GROUP BY user_id",
            'details' => "closed_courses",
        ];
        $queries[] = [
            'desc'    => _("Eingetragen in Wartelisten (wartend / vorläufig akzeptiert)"),
            'query'   => "SELECT CONCAT_WS(' / ', SUM(status = 'awaiting'), SUM(status = 'accepted'))
                  FROM admission_seminar_user
                  WHERE user_id = ?
                  GROUP BY user_id",
            'details' => "seminar_wait",
        ];
        $queries[] = [
            'desc'    => _("Eingetragen in Anmeldelisten"),
            'query'   => "SELECT COUNT(*)
                  FROM priorities
                  WHERE user_id = ?
                  GROUP BY user_id",
            'details' => "priorities",
        ];
        $queries[] = [
            'desc'  => _("Eingetragen in Einrichtungen (admin / dozent / tutor / autor)"),
            'query' => "SELECT CONCAT_WS(' / ', SUM(inst_perms = 'admin'), SUM(inst_perms = 'dozent'),
                                          SUM(inst_perms = 'tutor'), SUM(inst_perms = 'autor'))
                  FROM user_inst
                  WHERE user_id = ?
                  GROUP BY user_id",
        ];
        $queries[] = [
            'desc'  => _("Anzahl der Ankündigungen"),
            'query' => "SELECT COUNT(*) FROM news WHERE user_id = ? GROUP BY user_id",
        ];
        $queries[] = [
            'desc'  => _("Anzahl der Wikiseiten"),
            'query' => "SELECT COUNT(*) FROM wiki WHERE user_id = ? GROUP BY user_id",
        ];
        $queries[] = [
            'desc'  => _("Anzahl der Umfragen"),
            'query' => "SELECT COUNT(*) FROM questionnaires WHERE user_id = ? GROUP BY user_id",
        ];
        $queries[] = [
            'desc'  => _("Anzahl der Evaluationen"),
            'query' => "SELECT COUNT(*) FROM eval WHERE author_id = ? GROUP BY author_id",
        ];
        $queries[] = [
            'desc'    => _("Anzahl der Dateien in Veranstaltungen und Einrichtungen"),
            'query'   => "SELECT COUNT(file_refs.id)
                  FROM (file_refs INNER JOIN files ON file_refs.file_id = files.id)
                  INNER JOIN folders ON file_refs.folder_id = folders.id
                  WHERE (file_refs.user_id = ?)
                  AND (
                    (folders.range_type = 'course')
                    OR (folders.range_type = 'institute')
                  )
                  GROUP BY file_refs.user_id",
            'details' => "files",
        ];
        $queries[] = [
            'desc'    => _("Gesamtgröße der hochgeladenen Dateien in Veranstaltungen und Einrichtungen (in Megabytes)"),
            'query'   => "SELECT FORMAT(SUM(files.size)/1000000,2)
                  FROM (file_refs INNER JOIN files ON file_refs.file_id = files.id)
                  INNER JOIN folders ON file_refs.folder_id = folders.id
                  WHERE (file_refs.user_id = ?)
                  AND (files.filetype = 'StandardFile')
                  AND (
                    (folders.range_type = 'course')
                    OR (folders.range_type = 'institute')
                  )
                  GROUP BY file_refs.user_id",
            'details' => "files",
        ];

        foreach (PluginEngine::getPlugins('ForumModule') as $plugin) {
            $table     = $plugin->getEntryTableInfo();
            $queries[] = [
                'desc'  => $plugin->getPluginName() . ' - ' . _("Anzahl der Postings"),
                'query' => 'SELECT COUNT(*) FROM `' . $table['table'] . '`
            WHERE `' . $table['user_id'] . '` = ?
            GROUP BY `' . $table['user_id'] . '`',
            ];
        }

        // Evaluate queries
        foreach ($queries as $index => $query) {
            $statement = DBManager::get()->prepare($query['query']);
            $statement->execute([$user_id]);
            $queries[$index]['value'] = $statement->fetchColumn() ?: 0;
        }

        return $queries;
    }


    /**
     * Download documents
     * @param string $user_id
     * @param string $range_id
     */
    public function download_user_files_action($user_id, $range_id = null)
    {
        Seminar_Perm::get()->check('root');

        if ($range_id === null) {
            $file_refs = FileRef::findBySQL("INNER JOIN folders ON folders.id = file_refs.folder_id WHERE folders.range_type IN ('course','institute') AND file_refs.user_id = ? GROUP BY file_id ORDER BY NULL", [$user_id]);
        } else {
            $file_refs = FileRef::findBySQL("INNER JOIN folders ON folders.id = file_refs.folder_id WHERE folders.range_id = ? AND file_refs.user_id = ? GROUP BY file_id ORDER BY NULL", [$range_id, $user_id]);
        }

        $user = User::find($user_id);

        $archive_file_name = $user->username . '_files_' . date('Ymd-Hi') . '.zip';

        $archive_path = $GLOBALS['TMP_PATH'] . '/' . $archive_file_name;

        FileArchiveManager::createArchiveFromFileRefs(
            $file_refs,
            User::findCurrent(),
            $archive_path,
            false
        );


        $archive_download_link = FileManager::getDownloadURLForTemporaryFile(
            $archive_path,
            $archive_file_name
        );

        $this->redirect($archive_download_link);
    }

    /**
     * Course overview of a user
     */
    public function show_user_courses_action(User $user)
    {
        PageLayout::setTitle(sprintf(
            _('Veranstaltungsübersicht von %s'),
            $user->getFullName()
        ));
        $sem_courses = [];
        $courses = Course::findByUser($this->user->id);
        array_walk($courses,
            function (Course $course) use (&$sem_courses) {
                $semester_name = (string) $course->start_semester->name;
                if (!isset($sem_courses[$semester_name])) {
                    $sem_courses[$semester_name] = [];
                }
                $sem_courses[$semester_name][] = $course;
            }
        );

        $this->sem_courses = $sem_courses;
    }

    /**
     * Delete user from courses
     * @throws InvalidSecurityTokenException
     * @throws MethodNotAllowedException
     */
    public function delete_course_assignment_action(User $user)
    {
        CSRFProtection::verifyUnsafeRequest();

        if (Request::get('course_id')) {
            $courses = [Request::get('course_id')];
        } else {
            $courses = Request::getArray('courses');
        }

        if (empty($courses)) {
            PageLayout::postError(_('Sie haben keine Veranstaltungen ausgewählt.'));
        } else {
            $courses = array_map('Seminar::GetInstance', $courses);
            $successes = 0;
            $fails = 0;

            foreach ($courses as $course) {
                if ($course->deleteMember($user->id)) {
                    $successes++;
                } else {
                    $fails++;
                }
            }

            if ($successes) {
                PageLayout::postSuccess(sprintf(
                    ngettext(
                        '%s wurde aus %u Veranstaltung ausgetragen.',
                        '%s wurde aus %u Veranstaltungen ausgetragen.',
                        $successes
                    ),
                    htmlReady($user->getFullName()),
                    $successes
                ));
            }

            if ($fails) {
                PageLayout::postError(sprintf(
                    ngettext(
                        '%s konnte aus %u Veranstaltung nicht ausgetragen werden.',
                        '%s konnte aus %u Veranstaltungen nicht ausgetragen werden.',
                        $fails
                    ),
                    htmlReady($user->getFullName()),
                    $fails
                ));
            }
        }

        $this->redirect($this->show_user_coursesURL($user));
    }

    public function batch_export_members_action()
    {
        PageLayout::setTitle(_('Teilnehmendendaten exportieren'));

        $courseIds = Request::optionArray('export_members');
        $order = Config::get()->IMPORTANT_SEMNUMBER
            ? "ORDER BY `start_time` DESC, `VeranstaltungsNummer`, `Name`"
            : "ORDER BY `start_time` DESC,  `Name`";
        $this->courses = array_filter(
            Course::findMany($courseIds, $order),
            function (Course $course): bool {
                /*
                 * Check if sem_tree entries are allowed and may be changed and remove all courses
                 * where this is not the case.
                 */
                return !LockRules::Check($course->id, 'sem_tree', 'sem')
                    && $course->getSemClass()['bereiche'];
            }
        );

        // check if at least one course was selected (this can only happen from admin courses overview):
        if (count($this->courses) === 0) {
            PageLayout::postWarning('Es wurde keine Veranstaltung gewählt.');
            $this->relocate('admin/courses');
        }

    }

    /*
     * Export member data of all selected courses
     */
    public function do_batch_export_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        if (Request::submitted('xlsx')) {
            $export_format = 'xlsx';
        } else if (Request::submitted('csv')) {
            $export_format = 'csv';
        } else {
            PageLayout::postError('Nicht unterstütztes Exportformat.');
            $this->relocate('admin/courses');
        }

        $tmp_folder = $GLOBALS['TMP_PATH'] . '/temp_folder_' . md5(uniqid());
        mkdir($tmp_folder);

        $courses = Course::findMany(Request::optionArray('courses'));

        foreach ($courses as $course) {
            $header = ['Status', 'Anrede', 'Titel', 'Vorname', 'Nachname', 'Titel nachgestellt', 'Benutzername', 'Adresse', 'Telefonnr.',
                'E-Mail', 'Anmeldedatum', 'Matrikelnummer', 'Studiengänge'];
            $members = CourseMember::getMemberDataByCourse($course->seminar_id);

            foreach ($members as &$member) {
                $member['Anmeldedatum'] = $member['Anmeldedatum'] ? date('d.m.Y', $member['Anmeldedatum']) : _('unbekannt');
                unset($member['user_id']);
            }

            $filename = FileManager::cleanFileName('Teilnehmendenexport ' . $course->Name . '.' . $export_format);
            $filepath = $tmp_folder . '/'. $filename;

            $this->render_spreadsheet($header, $members, $export_format, $filename, $filepath);

        }
        $archive_file_path = $GLOBALS['TMP_PATH'] . '/archiv.zip';
        $archive_filename = 'Export_Teilnehmendendaten.zip';
        FileArchiveManager::createArchiveFromPhysicalFolder($tmp_folder, $archive_file_path);
        rmdirr($tmp_folder);
        $this->render_temporary_file($archive_file_path, $archive_filename, 'application/zip');
    }


    /**
     * Init sidebar
     */
    public function addSidebar()
    {
        $sidebar = Sidebar::Get();

        $actions = $sidebar->addWidget(new ActionsWidget());

        if (in_array('Standard', $GLOBALS['STUDIP_AUTH_PLUGIN'])) {
            $actions->addLink(
                _('Neues Konto anlegen'),
                $this->url_for('admin/user/new'),
                Icon::create('add')
            )->asDialog();
        }
        $actions->addLink(
            _('Vorläufiges Konto anlegen'),
            $this->url_for('admin/user/new/prelim'),
            Icon::create('add')
        )->asDialog();
        $actions->addLink(
            _('Konten zusammenführen'),
            $this->url_for('admin/user/migrate/' . ((!empty($this->user) && is_array($this->user)) ? $this->user['user_id'] : '')),
            Icon::create('community')
        );

        $search = $sidebar->addWidget(new SearchWidget());
        $search->addNeedle(_('Person suchen'),
            'user_id',
            true,
            new StandardSearch('user_id'),
            'function (value) { document.location = STUDIP.URLHelper.getURL("dispatch.php/admin/user/edit/" + value); }'
        );

        if ($this->action === 'index' && !empty($this->users)) {
            $export = $sidebar->addWidget(new ExportWidget());
            $export->addLink(_('Suchergebnis exportieren'),
                $this->url_for('admin/user?export=1'),
                Icon::create('export')
            );
        }

        if (empty($this->user) || !is_object($this->user)) {
            return;
        }

        $user_actions = new ActionsWidget();
        $user_actions->setTitle(sprintf(_('Aktionen für "%s"'), $this->user->username));

        $user_actions->addLink(
            _('Nachricht an Person verschicken'),
            URLHelper::getURL('dispatch.php/messages/write', ['rec_uname' =>  $this->user->username]),
            Icon::create('mail')
        )->asDialog();

        if ($this->user->locked) {
            $user_actions->addLink(
                _('Personenaccount entsperren'),
                $this->url_for("admin/user/unlock/{$this->user->id}"),
                Icon::create('lock-unlocked')
            )->asButton();
        } else {
            $user_actions->addLink(
                _('Personenaccount sperren'),
                $this->url_for("admin/user/lock_comment/{$this->user->id}"),
                Icon::create('lock-locked')
            )->asDialog('size=auto');
        }

        if ($this->user->auth_plugin !== null && ($GLOBALS['perm']->have_perm('root') || $GLOBALS['perm']->is_fak_admin() || !in_array($this->user->perms, words('root admin')))) {
            if (!StudipAuthAbstract::CheckField('auth_user_md5.password', $this->user->auth_plugin)) {
                $user_actions->addLink(
                    _('Passwortlink zusenden'),
                    $this->url_for("admin/user/change_password/{$this->user->id}"),
                    Icon::create('key')
                )->asButton();
            }
            $user_actions->addLink(
                _('Person löschen'),
                $this->url_for("admin/user/bulk/{$this->user->id}", ['method' => 'delete']),
                Icon::create('trash')
            )->asDialog('size=auto');
        }
        if (Config::get()->MAIL_NOTIFICATION_ENABLE && CourseMemberNotification::findOneBySQL("user_id = ?", [$this->user->user_id])) {
            $user_actions->addLink(
                _('Benachrichtigungen zurücksetzen'),
                $this->url_for("admin/user/reset_notification/{$this->user->id}"),
                Icon::create('refresh')
            )->asButton();
        }

        if ($this->action === 'activities') {
            $user_actions->addLink(
                _('Alle Dateien des Nutzers aus Veranstaltungen und Einrichtungen als ZIP herunterladen'),
                $this->url_for("admin/user/download_user_files/{$this->user->user_id}"),
                Icon::create('folder-full')
            )->asButton();
        }

        if ($this->user->id !== $GLOBALS['user']->id && TFASecret::exists($this->user->id)) {
            $user_actions->addLink(
                _('Zwei-Faktor-Authentifizierung deaktivieren'),
                $this->url_for("admin/user/reset_tfa/{$this->user->id}"),
                Icon::create('code-qr')
            )->asButton();
        }

        $sidebar->insertWidget($user_actions, 'actions', 'user_actions');

        // Privacy options
        if (Privacy::isVisible($this->user->user_id)) {
            $privacy = $sidebar->addWidget(new LinksWidget());
            $privacy->setTitle(_('Datenschutz'));

            $privacy->addLink(
                _('Anzeige Personendaten'),
                $this->url_for("privacy/landing/{$this->user->id}"),
                Icon::create('log')
            )->asDialog('size=medium');

            $privacy->addLink(
                _('Personendaten drucken'),
                $this->url_for("privacy/print/{$this->user->id}"),
                Icon::create('print'),
                ['class' => 'print_action', 'target' => '_blank']
            );

            $privacy->addLink(
                _('Export Personendaten als CSV'),
                $this->url_for("privacy/export/{$this->user->id}"),
                Icon::create('file-text')
            );

            $privacy->addLink(
                _('Export Personendaten als XML'),
                $this->url_for("privacy/xml/{$this->user->id}"),
                Icon::create('file-text')
            );

            $privacy->addLink(
                _('Export persönlicher Dateien als ZIP'),
                $this->url_for("privacy/filesexport/{$this->user->id}"),
                Icon::create('file-archive')
            );
        }

        $views = new ViewsWidget();
        $views->addLink(
            _('Zurück zur Übersicht'),
            $this->url_for('admin/user')
        )->setActive(false);
        $views->addLink(
            _('Person verwalten'),
            $this->url_for("admin/user/edit/{$this->user->id}")
        )->setActive($this->action === 'edit');
        $views->addLink(
            _('Zum Profil'),
            URLHelper::getURL('dispatch.php/profile', ['username' => $this->user->username]),
            Icon::create('person')
        );

        if ($GLOBALS['perm']->have_perm('root') && count($this->user)) {
            $views->addLink(
                _('Datei- und Aktivitätsübersicht'),
                $this->url_for("admin/user/activities/{$this->user->id}"),
                Icon::create('vcard')
            )->setActive($this->action === 'activities');


            if (Config::get()->LOG_ENABLE) {
                $views->addLink(
                    _('Personeneinträge im Log'),
                    URLHelper::getURL('dispatch.php/event_log/show?search=' . $this->user->username . '&type=user&object_id=' . $this->user->user_id),
                    Icon::create('log')
                );
            }



            $views->addLink(
                _('Zur Rollenverwaltung'),
                $this->url_for("admin/role/assign_role/{$this->user->id}"),
                Icon::create('roles2')
            );
        }
        $sidebar->insertWidget($views, 'user_actions', 'views');
    }
}
