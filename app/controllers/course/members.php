<?php

/*
 * MembersController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      David Siegfried <david.siegfried@uni-oldenburg.de>
 * @author      Sebastian Hobert <sebastian.hobert@uni-goettingen.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.5
 */

require_once 'lib/messaging.inc.php'; //Funktionen des Nachrichtensystems
require_once 'lib/export/export_studipdata_func.inc.php'; // Funktionne für den Export
require_once 'lib/export/export_linking_func.inc.php';


class Course_MembersController extends AuthenticatedController
{

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        global $perm;

        checkObject();
        checkObjectModule("participants");

        $this->course_id    = Context::getId();
        $this->course_title = Context::get()->Name;
        $this->user_id      = $GLOBALS['user']->id;
        $this->config       = CourseConfig::get($this->course_id);

        // Check perms
        $this->is_dozent = $perm->have_studip_perm('dozent', $this->course_id);
        $this->is_tutor  = $perm->have_studip_perm('tutor', $this->course_id);
        $this->is_autor  = $perm->have_studip_perm('autor', $this->course_id);

        if ($this->is_tutor) {
            PageLayout::setHelpKeyword("Basis.VeranstaltungenVerwaltenTeilnehmer");
        } else {
            PageLayout::setHelpKeyword("Basis.InVeranstaltungTeilnehmer");
        }

        // Check lock rules
        $this->dozent_is_locked = LockRules::Check($this->course_id, 'dozent');
        $this->tutor_is_locked  = LockRules::Check($this->course_id, 'tutor');
        $this->is_locked        = LockRules::Check($this->course_id, 'participants');

        // Layoutsettings
        PageLayout::setTitle(sprintf('%s - %s', Course::findCurrent()->getFullname(), _("Teilnehmende")));

        $this->studip_module = checkObjectModule('participants');
        object_set_visit_module( $this->studip_module->getPluginId());
        $this->last_visitdate = object_get_visit($this->course_id, $this->studip_module->getPluginId());

        // Check perms and set the last visit date
        if (!$this->is_tutor) {
            $this->last_visitdate = time() + 10;
        }

        // Get the max-page-value for the pagination
        $this->max_per_page = Config::get()->ENTRIES_PER_PAGE;
        $this->status_groups = [
            'dozent' => get_title_for_status('dozent', 2),
            'tutor' => get_title_for_status('tutor', 2),
            'autor' => get_title_for_status('autor', 2),
            'user' => get_title_for_status('user', 2),
            'accepted' => get_title_for_status('accepted', 2),
            'awaiting' => _("Wartende Personen"),
            'claiming' => _("Wartende Personen")
        ];

        // StatusGroups for the view
        $this->decoratedStatusGroups = [
            'dozent' => get_title_for_status('dozent', 1),
            'autor' => get_title_for_status('autor', 1),
            'tutor' => get_title_for_status('tutor', 1),
            'user' => get_title_for_status('user', 1)
        ];

        //check for admission / waiting list
        AdmissionApplication::addMembers($this->course->id);
        $this->checkUserVisibility();
    }

    public function index_action()
    {
        if (!$this->is_tutor && $this->config->COURSE_MEMBERS_HIDE) {
            throw new AccessDeniedException();
        }

        $sem                = Seminar::getInstance($this->course_id);
        $this->sort_by      = Request::option('sortby', 'nachname');
        $this->order        = Request::option('order', 'desc');
        $this->sort_status  = Request::get('sort_status', '');

        Navigation::activateItem('/course/members/view');
        if (Request::int('toggle')) {
            $this->order = $this->order == 'desc' ? 'asc' : 'desc';
        }

        $filtered_members = CourseMember::getMembers(
            $this->course_id,
            $this->sort_status,
            $this->sort_by . ' ' . $this->order
        );

        if ($this->is_tutor) {
            $filtered_members = array_merge(
                $filtered_members,
                AdmissionApplication::getAdmissionMembers(
                    $this->course_id,
                    $this->sort_status,
                    $this->sort_by . ' ' . $this->order
                )
            );
            $this->awaiting = $filtered_members['awaiting']->toArray('user_id username vorname nachname visible mkdate');
            $this->accepted = $filtered_members['accepted']->toArray('user_id username vorname nachname visible mkdate');
            $this->claiming = $filtered_members['claiming']->toArray('user_id username vorname nachname visible mkdate');
        }

        // Check autor-perms
        if (!$this->is_tutor) {
            // filter invisible user
            $this->invisibles = count($filtered_members['autor']->findBy('visible', 'no')) + count($filtered_members['user']->findBy('visible', 'no'));
            $current_user_id = $this->user_id;
            $exclude_invisibles =
                    function ($user) use ($current_user_id) {
                        return ($user['visible'] != 'no' || $user['user_id'] == $current_user_id);
                    };
            $filtered_members['autor'] = $filtered_members['autor']->filter($exclude_invisibles);
            $filtered_members['user'] = $filtered_members['user']->filter($exclude_invisibles);
            $this->my_visibility = $this->getUserVisibility();
            if (!$this->my_visibility['iam_visible']) {
                $this->invisibles--;
            }
        }

        // get member informations
        $this->dozenten = $filtered_members['dozent']->toArray('user_id username vorname nachname');
        $this->tutoren = $filtered_members['tutor']->toArray('user_id username vorname nachname mkdate');
        $this->autoren = $filtered_members['autor']->toArray('user_id username vorname nachname visible mkdate');
        $this->users = $filtered_members['user']->toArray('user_id username vorname nachname visible mkdate');
        $this->studipticket = Seminar_Session::get_ticket();
        $this->subject = $this->getSubject();
        $this->groups = $this->status_groups;
        $this->semAdmissionEnabled = false;
        // Check Seminar
        $this->waitingTitle = _('Warteliste (nicht aktiv)');
        $this->waiting_type = 'awaiting';
        if ($this->is_tutor && $sem->isAdmissionEnabled()) {
            $this->course = $sem;
            $distribution_time = $sem->getCourseSet()->getSeatDistributionTime();
            if ($sem->getCourseSet()->hasAlgorithmRun()) {
                $this->waitingTitle = _("Warteliste");
                if (!$sem->admission_disable_waitlist_move) {
                    $this->waitingTitle .= ' (' . _("automatisches Nachrücken ist eingeschaltet") . ')';
                } else {
                    $this->waitingTitle .= ' (' . _("automatisches Nachrücken ist ausgeschaltet") . ')';
                }
                $this->semAdmissionEnabled = 2;
                $this->waiting_type = 'awaiting';
            } else {
                $this->waitingTitle = sprintf(_("Anmeldeliste (Platzverteilung am %s)"), strftime('%x %R', $distribution_time));
                $this->semAdmissionEnabled = 1;
                $this->awaiting = $this->claiming;
                $this->waiting_type = 'claiming';
            }
        }
        // Set the infobox
        $this->createSidebar($filtered_members);

        if ($this->is_locked && $this->is_tutor) {
            $lockdata = LockRules::getObjectRule($this->course_id);
            if ($lockdata['description']) {
                PageLayout::postMessage(MessageBox::info(formatLinks($lockdata['description'])));
            }
        }
        $this->to_waitlist_actions = false;
        // Check for waitlist availability (influences available actions)
        // People can be moved to waitlist if waitlist available and no automatic moving up.
        if (!$sem->admission_disable_waitlist && $sem->admission_disable_waitlist_move
        && $sem->isAdmissionEnabled() && $sem->getCourseSet()->hasAlgorithmRun()) {
            $this->to_waitlist_actions = true;
        }
    }

    /*
     * Returns an array with emails of members
     */
    public function getEmailLinkByStatus($status, $members)
    {
        if (!Config::get()->ENABLE_EMAIL_TO_STATUSGROUP) {
            return;
        }

        if (in_array($status, words('accepted awaiting claiming'))) {
            $textStatus = _('Wartenden');
        } else {
            $textStatus = $this->status_groups[$status];
        }

        $results = SimpleCollection::createFromArray($members)->pluck('email');

        if (!empty($results)) {
            return sprintf('<a href="mailto:%s">%s</a>', htmlReady(join(',', $results)), Icon::create('mail', 'clickable', ['title' => sprintf('E-Mail an alle %s versenden',$textStatus)])->asImg(16));
        } else {
            return null;
        }
    }

    /**
     * Show dialog to enter a comment for this user
     * @param String $user_id
     * @throws AccessDeniedException
     */
    public function add_comment_action($user_id = null)
    {
        // Security Check
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }

        $course_member = CourseMember::find([$this->course_id, $user_id]);
        if (!$course_member) {
            $course_member = AdmissionApplication::find([$user_id, $this->course_id]);
        }
        if (is_null($course_member)) {
            throw new Trails_Exception(400);
        }
        $this->comment = $course_member->comment;
        $this->user = User::find($user_id);
        PageLayout::setTitle(sprintf(_('Bemerkung für %s'), htmlReady($this->user->getFullName())));

        // Output as dialog (Ajax-Request) or as Stud.IP page?
        $this->xhr = Request::isXhr();
        if ($this->xhr) {
            $this->set_layout(null);
        } else {
            Navigation::activateItem('/course/members/view');
        }
    }

    /**
     * Store a comment for this user
     * @param String $user_id
     * @throws AccessDeniedException
     */
    public function set_comment_action($user_id = null)
    {
        // Security Check
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }

        CSRFProtection::verifyUnsafeRequest();
        $course_member = CourseMember::find([$this->course_id, $user_id]);
        if (!$course_member) {
            $course_member = AdmissionApplication::find([$user_id, $this->course_id]);
        }
        if (!Request::submitted('save') || is_null($course_member)) {
            throw new Trails_Exception(400);
        }
        $course_member->comment = Request::get('comment');

        if ($course_member->store() !== false) {
            PageLayout::postSuccess(_('Bemerkung wurde erfolgreich gespeichert.'));
        } else {
            PageLayout::postError(_('Bemerkung konnte nicht erfolgreich gespeichert werden.'));
        }
        $this->redirect($this->indexURL());
    }

    /**
     * Add members to a seminar.
     * @throws AccessDeniedException
     */
    public function execute_multipersonsearch_autor_action()
    {
        // Security Check
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }

        // load MultiPersonSearch object
        $mp = MultiPersonSearch::load("add_autor" . $this->course_id);

        $countAdded = 0;
        $msg = [];
        foreach ($mp->getAddedUsers() as $a) {
            if ($this->addMember($a, true, Request::bool('consider_contingent'), $msg)) {
                $countAdded++;
            }
        }

        if ($countAdded == 1) {
            $text = _('Es wurde eine neue Person hinzugefügt.');
        } else {
            $text = sprintf(_('Es wurden %s neue Personen hinzugefügt.'), $countAdded);
        }
        PageLayout::postSuccess($text, $msg);
        $this->redirect($this->indexURL());
    }

     /**
     * Add dozents to a seminar.
     * @throws AccessDeniedException
     */
    public function execute_multipersonsearch_dozent_action()
    {
        // Security Check
        if (!$this->is_dozent) {
            throw new AccessDeniedException('Sie sind nicht bereichtig, auf diesen Bereich von Stud.IP zuzugreifen.');
        }

        // load MultiPersonSearch object
        $mp = MultiPersonSearch::load("add_dozent" . $this->course_id);
        $sem = Seminar::GetInstance($this->course_id);
        $countAdded = 0;
        foreach ($mp->getAddedUsers() as $a) {
            if($this->addDozent($a)) {
                $countAdded++;
            }
        }
        if($countAdded > 0) {
            $status = get_title_for_status('dozent', $countAdded, $sem->status);
            if ($countAdded == 1) {
                PageLayout::postSuccess(sprintf(_('Ein %s wurde hinzugefügt.'), htmlReady($status)));
            } else {
                PageLayout::postSuccess(sprintf(_("Es wurden %s %s Personen hinzugefügt."), $countAdded, htmlReady($status)));
            }
        }

        $this->redirect('course/members/index');
    }

    /**
     * Add people to a course waitlist.
     * @throws AccessDeniedException
     */
    public function execute_multipersonsearch_waitlist_action()
    {
        // Security Check
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }

        // load MultiPersonSearch object
        $mp = MultiPersonSearch::load('add_waitlist' . $this->course_id);
        $countAdded = 0;
        $countFailed = 0;
        foreach ($mp->getAddedUsers() as $a) {
            if ($this->addToWaitlist($a)) {
                $countAdded++;
            } else {
                $countFailed++;
            }
        }

        if ($countAdded) {
            PageLayout::postSuccess(sprintf(ngettext('Es wurde %u neue Person auf der Warteliste hinzugefügt.',
                'Es wurden %u neue Personen auf der Warteliste hinzugefügt.', $countAdded), $countAdded));
        }
        if ($countFailed) {
            PageLayout::postError(sprintf(ngettext('%u Person konnte nicht auf die Warteliste eingetragen werden.',
                '%u neue Personen konnten nicht auf die Warteliste eingetragen werden.', $countFailed),
                $countFailed));
        }
        $this->redirect('course/members/index');
    }

    /**
     * Helper function to add dozents to a seminar.
     */
    private function addDozent($dozent)
    {
        $sem = Seminar::GetInstance($this->course_id);
        if ($sem->addMember($dozent, "dozent")) {
            // Only applicable when globally enabled and user deputies enabled too
            if (Config::get()->DEPUTIES_ENABLE) {
                // Check whether chosen person is set as deputy
                // -> delete deputy entry.
                $deputy = Deputy::find([$this->course_id, $dozent]);
                if ($deputy) {
                    $deputy->delete();
                }
                // Add default deputies of the chosen lecturer...
                if (Config::get()->DEPUTIES_DEFAULTENTRY_ENABLE) {
                    $deputies = Deputy::findDeputies($dozent)->pluck('user_id');
                    $lecturers = $sem->getMembers();
                    foreach ($deputies as $deputy) {
                        // ..but only if not already set as lecturer or deputy.
                        if (!isset($lecturers[$deputy['user_id']]) && !Deputy::isDeputy($deputy['user_id'], $this->course_id)) {
                            Deputy::addDeputy($deputy['user_id'], $this->course_id);
                        }
                    }
                }
            }
           return true;
        } else {
            return false;
        }
    }

    /**
     * Add tutors to a seminar.
     * @throws AccessDeniedException
     */
    public function execute_multipersonsearch_tutor_action()
    {
        // Security Check
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }

        // load MultiPersonSearch object
        $mp = MultiPersonSearch::load("add_tutor" . $this->course_id);
        $sem = Seminar::GetInstance($this->course_id);
        $countAdded = 0;
        foreach ($mp->getAddedUsers() as $a) {
            if ($this->addTutor($a)) {
                $countAdded++;
            }
        }
        if($countAdded) {
            PageLayout::postSuccess(sprintf(_('%s wurde hinzugefügt.'), htmlReady(get_title_for_status('tutor', $countAdded, $sem->status))));
        }
        $this->redirect('course/members/index');
    }

    private function addTutor($tutor) {
        $sem = Seminar::GetInstance($this->course_id);
        if ($sem->addMember($tutor, "tutor")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Provides a dialog to move or copy selected users to another course.
     */
    public function select_course_action()
    {
        if (Request::submitted('submit')) {
            CSRFProtection::verifyUnsafeRequest();
            $this->flash['users_to_send'] = Request::getArray('users');
            $this->flash['target_course'] = Request::option('course_id');
            $this->flash['move'] = Request::int('move');
            $this->redirect('course/members/send_to_course');
        } else {
            global $perm;
            if ($perm->have_perm('root')) {
                $parameters = [
                    'semtypes' => studygroup_sem_types() ?: null,
                    'exclude' => [Context::getId()],
                ];
            } else if ($perm->have_perm('admin')) {
                $parameters = [
                    'semtypes' => studygroup_sem_types() ?: null,
                    'institutes' => array_map(function ($i) {
                        return $i['Institut_id'];
                    }, Institute::getMyInstitutes()),
                    'exclude' => [Context::getId()],
                ];

            } else {
                $parameters = [
                    'userid' => $GLOBALS['user']->id,
                    'semtypes' => studygroup_sem_types() ?: null,
                    'exclude' => [Context::getId()],
                ];
            }
            $coursesearch = MyCoursesSearch::get('Seminar_id', $GLOBALS['perm']->get_perm(), $parameters);
            $this->search = QuickSearch::get('course_id', $coursesearch)
                ->setInputStyle('width:100%')
                ->withButton()
                ->render();
            $this->course_id = Request::option('course_id');
            $this->course_id_parameter = Request::get('course_id_parameter');
            if (!empty($this->flash['users']) || Request::getArray('users')) {
                $users = $this->flash['users'] ?: Request::getArray('users');
                // create a usable array
                foreach ($users as $user => $val) {
                    if ($val) {
                        $this->users[] = $user;
                    }
                }

                PageLayout::setTitle( _('Zielveranstaltung auswählen'));
            } elseif (Request::isXhr()) {
                $this->response->add_header('X-Dialog-Close', '1');
                $this->render_nothing();
            } else {
                $this->redirect('course/members/index');
            }
        }
    }

    /**
     * Copies or moves selected users to the selected target course.
     */
    public function send_to_course_action()
    {
        if ($target = $this->flash['target_course']) {
            $msg = $this->sendToCourse(
                (array)$this->flash['users_to_send'],
                $target,
                $this->flash['move']
            );
            if ($msg['success']) {
                if (count($msg['success']) === 1) {
                    $text = _('Eine Person wurde in die Zielveranstaltung eingetragen.');
                } else {
                    $text = sprintf(
                        _('%s Person(en) wurde(n) in die Zielveranstaltung eingetragen.'),
                        count($msg['success'])
                    );
                }
                PageLayout::postSuccess($text);
            }
            if ($msg['existing']) {
                if (count($msg['existing']) === 1) {
                    $text = _('Eine Person ist bereits in die Zielveranstaltung eingetragen ' .
                                'und kann daher nicht verschoben/kopiert werden.');
                } else {
                    $text = sprintf(_('%s Person(en) sind bereits in die Zielveranstaltung eingetragen ' .
                        'und konnten daher nicht verschoben/kopiert werden.'),
                        sizeof($msg['existing']));
                }
                PageLayout::postInfo($text);
            }
            if ($msg['failed']) {
                if (count($msg['failed']) === 1) {
                    $text = _('Eine Person kann nicht in die Zielveranstaltung eingetragen werden.');
                } else {
                    $text = sprintf(_('%s Person(en) konnten nicht in die Zielveranstaltung eingetragen werden.'),
                            sizeof($msg['failed']));
                }
                PageLayout::postError($text);
            }
        } else {
            PageLayout::postError(_('Bitte wählen Sie eine Zielveranstaltung.'));
        }
        $this->redirect($this->indexURL());
    }

    /**
     * Send Stud.IP-Message to selected users
     */
    public function send_message_action()
    {
        if (!empty($this->flash['users'])) {
            // create a usable array
            foreach ($this->flash['users'] as $user => $val) {
                if ($val) {
                    $users[] = User::find($user)->username;
                }
            }
            $_SESSION['sms_data'] = [];
            $_SESSION['sms_data']['p_rec'] = array_filter($users);
            $this->redirect(URLHelper::getURL('dispatch.php/messages/write', [
                'default_subject' => $this->getSubject(),
                'tmpsavesnd' => 1,
                'emailrequest' => 1
            ]));
        } else {
            if (Request::isXhr()) {
                $this->response->add_header('X-Dialog-Close', '1');
                $this->render_nothing();
            } else {
            $this->redirect('course/members/index');
        }
    }
    }

    public function import_autorlist_action()
    {
        if (!Request::isXhr()) {
            Navigation::activateItem('/course/members/view');
        }
        $datafields = DataField::getDataFields('user', 1 | 2 | 4 | 8, true);
        $accessible_df = [];
        foreach ($datafields as $df) {
            if ($df->accessAllowed() && in_array($df->getId(), $GLOBALS['TEILNEHMER_IMPORT_DATAFIELDS'])) {
                $accessible_df[] = $df;
            }
        }
        $this->accessible_df = $accessible_df;

    }

    /**
     * Old version of CSV import (copy and paste from teilnehmer.php
     *
     * @throws AccessDeniedException
     */
    public function set_autor_csv_action()
    {
        // Security Check
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }
        CSRFProtection::verifyUnsafeRequest();

        // prepare CSV-Lines
        $messaging = new messaging();
        $csv_request = preg_split('/(\n\r|\r\n|\n|\r)/', trim(Request::get('csv_import')));
        $csv_mult_founds = [];
        $csv_count_insert = 0;
        $csv_count_multiple = 0;
        $csv_count_double = 0;
        $datafield_id = null;

        if (Request::get('csv_import_format') && !in_array(Request::get('csv_import_format'), words('realname username email'))) {
            foreach (DataField::getDataFields('user', 1 | 2 | 4 | 8, true) as $df) {
                if ($df->accessAllowed() && in_array($df->getId(), $GLOBALS['TEILNEHMER_IMPORT_DATAFIELDS']) && $df->getId() == Request::quoted('csv_import_format')) {
                    $datafield_id = $df->getId();
                    break;
                }
            }
        }
        $csv_count_contingent_full = 0;
        $csv_count_present = 0;
        $csv_not_found = [];
        $consider_contingent = false;
        if (Request::get('csv_import')) {
            // remove duplicate users from csv-import
            $csv_lines = array_unique($csv_request);
            foreach ($csv_lines as $csv_line) {
                $csv_name = preg_split('/[,\t]/', mb_substr($csv_line, 0, 100), -1, PREG_SPLIT_NO_EMPTY);
                $csv_nachname = trim($csv_name[0]);
                $csv_vorname = trim($csv_name[1]);

                if (!$csv_nachname) {
                    continue;
                }

                if (Request::option('csv_import_format') === 'realname') {
                    $csv_users = CourseMember::getMemberByIdentification($this->course_id, $csv_nachname, $csv_vorname);
                } elseif (Request::option('csv_import_format') === 'username') {
                    $csv_users = CourseMember::getMemberByUsername($this->course_id, $csv_nachname);
                } elseif (Request::option('csv_import_format') === 'email') {
                    $csv_users = CourseMember::getMemberByEmail($this->course_id, $csv_nachname);
                } else {
                    $csv_users = CourseMember::getMemberByDatafield($this->course_id, $csv_nachname, $datafield_id);
                }

                // if found more then one result to given name
                if (count($csv_users) > 1) {
                    // if user have two accounts
                    foreach ($csv_users as $row) {
                        if ($row['is_present']) {
                            $csv_count_double++;
                        } else {
                            $csv_mult_founds[$csv_line][] = $row;
                        }
                    }

                    if (is_array($csv_mult_founds[$csv_line])) {
                        $csv_count_multiple++;
                    }
                } elseif (count($csv_users) > 0) {
                    $row = reset($csv_users);
                    if (!$row['is_present']) {
                        $consider_contingent = Request::option('consider_contingent_csv');

                        if (CourseMember::insertCourseMember($this->course_id, $row['user_id'], 'autor', isset($consider_contingent), $consider_contingent)) {
                            $csv_count_insert++;
                            setTempLanguage($this->user_id);

                            $message = sprintf(_('Sie wurden in die Veranstaltung **%s** eingetragen.'), $this->course_title);

                            restoreLanguage();
                            $messaging->insert_message($message, $row['username'], '____%system%____', FALSE, FALSE, '1', FALSE, sprintf('%s %s', _('Systemnachricht:'), _('Eintragung in Veranstaltung')), TRUE);
                        } elseif (isset($consider_contingent)) {
                            $csv_count_contingent_full++;
                        }
                    } else {
                        $csv_count_present++;
                    }
                } else {
                    // not found
                    $csv_not_found[] = stripslashes($csv_nachname) . ($csv_vorname ? ', ' . stripslashes($csv_vorname) : '');
                }
            }
        }
        $selected_users = Request::getArray('selected_users');

        if (!empty($selected_users) && count($selected_users) > 0) {
            foreach ($selected_users as $selected_user) {
                if ($selected_user) {
                    if (CourseMember::insertCourseMember($this->course_id, get_userid($selected_user), 'autor', isset($consider_contingent), $consider_contingent)) {
                        $csv_count_insert++;
                        setTempLanguage($this->user_id);
                        $message = sprintf(_('Sie wurden manu

                        ell in die Veranstaltung **%s** eingetragen.'), $this->course_title);

                        restoreLanguage();
                        $messaging->insert_message($message, $selected_user, '____%system%____', FALSE, FALSE, '1', FALSE, sprintf('%s %s', _('Systemnachricht:'), _('Eintragung in Veranstaltung')), TRUE);
                    } elseif (isset($consider_contingent)) {
                        $csv_count_contingent_full++;
                    }
                }
            }
        }

        // no results
        if (empty($csv_lines) && empty($selected_users)) {
            PageLayout::postError(_("Niemanden gefunden!"));
        }

        if ($csv_count_insert) {
            PageLayout::postSuccess(sprintf(_('%s Personen in die Veranstaltung eingetragen!'), $csv_count_insert));
        }

        if ($csv_count_present) {
            PageLayout::postInfo(sprintf(_('%s Personen waren bereits in der Veranstaltung eingetragen!'), $csv_count_double + $csv_count_present));
        }

        // redirect to manual assignment
        if ($csv_mult_founds) {
            PageLayout::postInfo(sprintf(_('%s Personen konnten <b>nicht eindeutig</b>
                zugeordnet werden! Nehmen Sie die Zuordnung bitte manuell vor.'), $csv_count_multiple));
            $this->flash['csv_mult_founds'] = $csv_mult_founds;
            $this->redirect('course/members/csv_manual_assignment');
            return;
        }
        if (is_array($csv_not_found) && count($csv_not_found) > 0) {
            PageLayout::postError(sprintf(_('%s konnten <b>nicht</b> zugeordnet werden!'), htmlReady(join(',', $csv_not_found))));
        }

        if ($csv_count_contingent_full) {
            PageLayout::postError(sprintf(_('%s Personen konnten <b>nicht</b> zugeordnet werden, da das ausgewählte Kontingent keine freien Plätze hat.'),
                $csv_count_contingent_full));
        }

        $this->relocate('course/members/index');
    }

    /**
     * Select manual the assignment of a given user or of a group of users
     * @global Object $perm
     * @throws AccessDeniedException
     */
    public function csv_manual_assignment_action()
    {
        // Security. If user not autor, then redirect to index
        if (!$GLOBALS['perm']->have_studip_perm('tutor', $this->course_id)) {
            throw new AccessDeniedException();
        }

        if (empty($this->flash['csv_mult_founds'])) {
            $this->redirect('course/members/index');
        }
    }

    /**
     * Change the visibilty of an autor
     * @return void
     */
    public function change_visibility_action($cmd, $mode)
    {
        // Security. If user not autor, then redirect to index
        if ($GLOBALS['perm']->have_studip_perm('tutor', $this->course_id)) {
            throw new AccessDeniedException();
        }

        // Check for visibile mode
        if ($cmd === 'make_visible') {
            $command = 'yes';
        } else {
            $command = 'no';
        }

        if ($mode === 'awaiting') {
            $model = AdmissionApplication::findOneBySQL(
                'user_id = ? AND seminar_id = ?',
                [$this->user_id, $this->course_id]
            );
        } else {
            $model = CourseMember::findOneBySQL(
                'user_id = ? AND Seminar_id = ?',
                [$this->user_id, $this->course_id]
            );
        }
        $model->visible = $command;
        $result = $model->store();

        if ($result > 0) {
            PageLayout::postSuccess(_('Ihre Sichtbarkeit wurde erfolgreich geändert.'));
        } else {
            PageLayout::postError(_('Leider ist beim Ändern der Sichtbarkeit ein Fehler aufgetreten. Die Einstellung konnte nicht vorgenommen werden.'));
        }
        $this->redirect('course/members/index');
    }

    /**
     * Helper function to select the action
     * @throws AccessDeniedException
     */
    public function edit_tutor_action()
    {
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }
        CSRFProtection::verifyUnsafeRequest();

        $this->flash['users'] = Request::getArray('tutor');

        // select the additional method
        switch (Request::get('action_tutor')) {
            case 'downgrade':
                $target = 'course/members/downgrade_user/tutor/autor';
                break;
            case 'remove':
                $target = 'course/members/cancel_subscription/collection/tutor';
                break;
            case 'message':
                $this->redirect('course/members/send_message');
                return;
                break;
            default:
                $target = 'course/members/index';
                break;
        }
        $this->relocate($target);
    }

    /**
     * Helper function to select the action
     * @throws AccessDeniedException
     */
    public function edit_autor_action()
    {
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }
        CSRFProtection::verifyUnsafeRequest();

        $this->flash['users'] = Request::getArray('autor');

        switch (Request::get('action_autor')) {
            case 'upgrade':
                $target = 'course/members/upgrade_user/autor/tutor';
                break;
            case 'downgrade':
                $target = 'course/members/downgrade_user/autor/user';
                break;
            case 'to_admission_first':
                $target = 'course/members/to_waitlist/first';
                break;
            case 'to_admission_last':
                $target = 'course/members/to_waitlist/last';
                break;
            case 'remove':
                $target = 'course/members/cancel_subscription/collection/autor';
                break;
            case 'to_course':
                $this->redirect('course/members/select_course');
                return;
                break;
            case 'message':
                $this->redirect('course/members/send_message');
                return;
                break;
            default:
                $target = 'course/members/index';
                break;
        }
        $this->relocate($target);
    }

    /**
     * Helper function to select the action
     * @throws AccessDeniedException
     */
    public function edit_user_action()
    {
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }
        CSRFProtection::verifyUnsafeRequest();

        $this->flash['users'] = Request::getArray('user');
        $this->flash['consider_contingent'] = Request::get('consider_contingent');

        // select the additional method
        switch (Request::get('action_user')) {
            case 'upgrade':
                $target = 'course/members/upgrade_user/user/autor';
                break;
            case 'to_admission_first':
                $target = 'course/members/to_waitlist/first';
                break;
            case 'to_admission_last':
                $target = 'course/members/to_waitlist/last';
                break;
            case 'remove':
                $target = 'course/members/cancel_subscription/collection/user';
                break;
            case 'to_course':
                $this->redirect('course/members/select_course');
                return;
                break;
            case 'message':
                $this->redirect('course/members/send_message');
                return;
                break;
            default:
                $target = 'course/members/index';
                break;
        }
        $this->relocate($target);
    }

    /**
     * Helper function to select the action
     * @throws AccessDeniedException
     */
    public function edit_awaiting_action()
    {
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }
        CSRFProtection::verifyUnsafeRequest();

        $this->flash['users'] = Request::getArray('awaiting');
        $this->flash['consider_contingent'] = Request::get('consider_contingent');
        $waiting_type = Request::option('waiting_type');
        // select the additional method
        switch (Request::get('action_awaiting')) {
            case 'upgrade_autor':
                $target = 'course/members/insert_admission/awaiting/collection';
                break;
            case 'upgrade_user':
                $target = 'course/members/insert_admission/awaiting/collection/user';
                break;
            case 'remove':
                $target = 'course/members/cancel_subscription/collection/' . $waiting_type;
                break;
            case 'message':
                $this->redirect('course/members/send_message');
                return;
                break;
            default:
                $target = 'course/members/index';
                break;
        }
        $this->relocate($target);
    }

    /**
     * Helper function to select the action
     * @throws AccessDeniedException
     */
    public function edit_accepted_action()
    {
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }
        CSRFProtection::verifyUnsafeRequest();

        $this->flash['users'] = Request::getArray('accepted');
        $this->flash['consider_contingent'] = Request::get('consider_contingent');


        // select the additional method
        switch (Request::get('action_accepted')) {
            case 'upgrade':
                $target = 'course/members/insert_admission/accepted/collection';
                break;
            case 'remove':
                $target = 'course/members/cancel_subscription/collection/accepted';
                break;
            case 'message':
                $this->redirect('course/members/send_message');
                return;
                break;
            default:
                $target = 'course/members/index';
                break;
        }
        $this->relocate($target);
    }

    /**
     * Insert a user to a given seminar or a group of users
     * @param String $status
     * @param String $cmd
     * @param String $target_status
     * @throws AccessDeniedException
     */
    public function insert_admission_action($status, $cmd, $target_status = 'autor')
    {
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }

        if (isset($this->flash['consider_contingent'])) {
            Request::set('consider_contingent', $this->flash['consider_contingent']);
        }

        $users = [];
        // create a usable array
        if($this->flash['users']) {
            $users = array_filter($this->flash['users'], function ($user) {
                return $user;
            });
        }

        if ($users) {
            $msgs = $this->insertAdmissionMember(
                $users,
                $target_status,
                Request::bool('consider_contingent'),
                $status === 'accepted'
            );
            if ($msgs) {
                if ($cmd === 'add_user') {
                    $message = sprintf(_('%s wurde in die Veranstaltung mit dem Status <b>%s</b> eingetragen.'), htmlReady(join(',', $msgs)), $this->decoratedStatusGroups['autor']);
                } else {
                    if ($status === 'awaiting') {
                        $message = sprintf(_('%s wurde aus der Anmelde bzw. Warteliste mit dem Status
                            <b>%s</b> in die Veranstaltung eingetragen.'), htmlReady(join(', ', $msgs)), $this->decoratedStatusGroups[$target_status]);
                    } else {
                        $message = sprintf(_('%s wurde mit dem Status <b>%s</b> endgültig akzeptiert
                            und damit in die Veranstaltung aufgenommen.'), htmlReady(join(', ', $msgs)), $this->decoratedStatusGroups[$target_status]);
                    }
                }

                PageLayout::postSuccess($message);
            } else {
                $message = _("Es stehen keine weiteren Plätze mehr im Teilnehmendenkontingent zur Verfügung.");
                PageLayout::postError($message);
            }
        } else {
            PageLayout::postError(_('Sie haben niemanden zum Hochstufen ausgewählt.'));
        }

        $this->redirect('course/members/index');
    }

    /**
     * Cancel the subscription of a selected user or group of users
     * @param String $cmd
     * @param String $status
     * @param String $user_id
     * @throws AccessDeniedException
     */
    public function cancel_subscription_action($cmd, $status, $user_id = null)
    {
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }
        $course = Seminar::GetInstance($this->course_id);
        if (!Request::submitted('no')) {
            if (Request::submitted('yes')) {
                CSRFProtection::verifyUnsafeRequest();
                $users = Request::getArray('users');
                if (!empty($users)) {
                    if (in_array($status, words('accepted awaiting claiming'))) {
                        $msgs = $course->cancelAdmissionSubscription($users, $status);
                    } else {
                        $msgs = $course->cancelSubscription($users);
                    }

                    // deleted authors
                    if (!empty($msgs)) {
                        if (count($msgs) <= 5) {
                            PageLayout::postSuccess(sprintf(
                                _("%s %s wurde aus der Veranstaltung ausgetragen."),
                                htmlReady($this->status_groups[$status]),
                                htmlReady(join(', ', $msgs))
                            ));
                        } else {
                            PageLayout::postSuccess(sprintf(
                                _("%u %s wurden aus der Veranstaltung entfernt."),
                                count($msgs),
                                htmlReady($this->status_groups[$status])
                            ));
                        }
                    }
                } else {
                    PageLayout::postWarning(sprintf(
                        _('Sie haben keine %s zum Austragen ausgewählt'),
                        $this->status_groups[$status]
                    ));
                }
            } else {
                if ($cmd === 'singleuser') {
                    $users = [$user_id];
                } else {
                    // create a usable array
                    foreach ($this->flash['users'] as $user => $val) {
                        if ($val) {
                            $users[] = $user;
                        }
                    }
                }

                PageLayout::postQuestion(
                    sprintf(
                        _('Wollen Sie die/den "%s" wirklich austragen?'),
                        htmlReady($this->status_groups[$status])
                    )
                )->setAcceptURL(
                    $this->cancel_subscriptionURL('collection', $status),
                    compact('users')
                );
                $this->flash['checked'] = $users;
            }
        }
        $this->redirect($this->indexURL());
    }

    /**
     * Upgrade a user to a selected status
     * @param string $status
     * @param string $next_status
     * @throws AccessDeniedException
     */
    public function upgrade_user_action($status, $next_status)
    {
         if ($GLOBALS['perm']->have_studip_perm('tutor', $this->course_id)
            && $next_status !== 'autor'
            && !$GLOBALS['perm']->have_studip_perm('dozent', $this->course_id)) {
            throw new AccessDeniedException();
        }

        // create a usable array
        if(!empty($this->flash['users'])) {
            foreach ($this->flash['users'] as $user => $val) {
                if ($val) {
                    $users[] = $user;
                }
            }
        }

        if (!empty($users)) {
            // insert admission user to autorlist
            $msgs = $this->setMemberStatus($users, $status, $next_status, 'upgrade');

            if ($msgs['success']) {
                PageLayout::postSuccess(sprintf(
                    _('Das Hochstufen auf den Status  %s von %s wurde erfolgreich durchgeführt'),
                    htmlReady($this->decoratedStatusGroups[$next_status]),
                    htmlReady(join(', ', $msgs['success']))
                ));
            }

            if ($msgs['no_tutor']) {
                PageLayout::postError(sprintf(
                    _('Das Hochstufen auf den Status %s von %s konnte nicht durchgeführt werden, weil die globale Rechtestufe "tutor" fehlt.') . ' ' . _('Bitte wenden Sie sich an den Support.'),
                    htmlReady($this->decoratedStatusGroups[$next_status]),
                    htmlReady(join(', ', $msgs['no_tutor']))
                ));
            }
        } else {
            PageLayout::postError(sprintf(
                _('Sie haben keine %s zum Hochstufen ausgewählt'),
                htmlReady($this->status_groups[$status])
            ));
        }

        $this->redirect('course/members/index');
    }

    /**
     * Downgrade a user to a selected status
     * @param string $status
     * @param string $next_status
     * @throws AccessDeniedException
     */
    public function downgrade_user_action($status, $next_status)
    {
        // Security Check
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }

        if ($next_status !== 'user' && !$this->is_dozent) {
            throw new AccessDeniedException();
        }

        if (!empty($this->flash['users'])) {
            foreach ($this->flash['users'] as $user => $val) {
                if ($val) {
                    $users[] = $user;
                }
            }
        }

        if (!empty($users)) {
            $msgs = $this->setMemberStatus($users, $status, $next_status, 'downgrade');

            if ($msgs['success']) {
                PageLayout::postSuccess(sprintf(
                    _('Der/die %s %s wurde auf den Status %s heruntergestuft.'),
                    htmlReady($this->decoratedStatusGroups[$status]),
                    htmlReady(join(', ', $msgs['success'])),
                    $this->decoratedStatusGroups[$next_status]));
            }
        } else {
            PageLayout::postError(sprintf(
                _('Sie haben keine %s zum Herunterstufen ausgewählt'),
                htmlReady($this->status_groups[$status])
            ));
        }

        $this->redirect('course/members/index');
    }

    /**
     * Moves selected users to waitlist, either at the top or at the end.
     * @param $which_end 'first' or 'last': append to top or to end of waitlist?
     */
    public function to_waitlist_action($which_end)
    {
        // Security Check
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }

        $users = [];
        if (!empty($this->flash['users'])) {
            $users = array_keys(array_filter($this->flash['users']));
        }

        if (!empty($users)) {
            $msg = $this->moveToWaitlist($users, $which_end);
            if (count($msg['success'])) {
                PageLayout::postSuccess(sprintf(_('%s Person(en) wurden auf die Warteliste verschoben.'),
                    count($msg['success'])),
                    count($msg['success']) <= 5 ? $msg['success'] : []);
            }
            if (count($msg['errors'])) {
                PageLayout::postError(sprintf(_('%s Person(en) konnten nicht auf die Warteliste verschoben werden.'),
                    count($msg['errors'])),
                    count($msg['error']) <= 5 ? $msg['error'] : []);
            }
        } else {
            PageLayout::postError(_('Sie haben keine Personen zum Verschieben auf die Warteliste ausgewählt'));
        }

        $this->redirect('course/members/index');
    }

    /**
     * Displays all members of the course and their aux data
     */
    public function additional_action($format = null)
    {
        // Users get forwarded to aux_input
        if (!($this->is_dozent || $this->is_tutor)) {
            $this->redirect('course/members/additional_input');
            return;
        }

        Navigation::activateItem('/course/members/additional');

        // fetch course and aux data
        $course    = Course::findCurrent();
        $this->aux = $course->aux->getCourseData($course);

        $export_widget = new ExportWidget();
        $export_widget->addLink(
            _('Zusatzangaben exportieren'),
            $this->export_additionalURL(),
            Icon::create('file-excel')
        );

        Sidebar::Get()->addWidget($export_widget);
    }

    /**
     * Stora all members of the course and their aux data
     */
    public function store_additional_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        $course = Course::findCurrent();

        foreach ($course->members->findBy('status', 'autor') as $member) {
            $course->aux->updateMember($member, Request::getArray($member->user_id));
        }

        $this->redirect($this->additionalURL());
    }

    /**
     * Export all members of the course and their aux data to CSV
     */
    public function export_additional_action()
    {
        $course  = Course::findCurrent();
        $aux     = $course->aux->getCourseData($course, true);
        $tmpname = md5(uniqid('Zusatzangaben'));

        if(array_to_csv($aux['rows'], $GLOBALS['TMP_PATH'] . '/' . $tmpname, $aux['head'])) {
            $this->redirect(
                FileManager::getDownloadURLForTemporaryFile(
                    $tmpname,
                    _('Zusatzangaben') . '.csv'
                )
            );
        }
    }

    /**
     * Aux input for users
     */
    public function additional_input_action()
    {
        // Activate the autoNavi otherwise we dont find this page in navi
        Navigation::activateItem('/course/members/additional');

        // Fetch datafields for the user
        $course = Course::findCurrent();
        $member = $course->members->findOneBy('user_id', $GLOBALS['user']->id);
        $this->datafields = $member ? $course->aux->getMemberData($member) : [];

        $this->editable = false;

        // We need aux data in the view
        $this->aux = $course->aux;

        // Update em if they got submittet
        if (Request::submitted('save')) {
            $success = 0;

            $datafields = SimpleCollection::createFromArray($this->datafields);
            foreach (Request::getArray('aux') as $aux => $value) {
                $datafield = $datafields->findOneBy('datafield_id', $aux);
                if ($datafield) {
                    $typed = $datafield->getTypedDatafield();
                    if ($typed->isEditable()) {
                        $typed->setValueFromSubmit($value);
                        // Track success across each store process.
                        $success = $success + $typed->store();
                    }
                }
            }

            // Show success or error message.
            if ($success > 0) {
                PageLayout::postSuccess(_('Die Daten wurden gespeichert.'));
            } else {
                PageLayout::postWarning(_('Keine Veränderungen vorgenommen.'));
            }
        } else if ($course->aux_lock_rule_forced) {
            if (empty(array_column($this->datafields, 'content'))) {
                PageLayout::postWarning(_('Um die Anmeldung zur Veranstaltung abzuschließen, müssen Sie zusätzliche Angaben auf dieser Seite machen.'));
            }
        }
    }

    /**
     * Get the visibility of a user in a seminar
     * @return Array
     */
    private function getUserVisibility()
    {
        $member = CourseMember::find([$this->course_id, $this->user_id]);

        $visibility = $member->visible;
        $status = $member->status;
        $result['visible_mode'] = false;

        if ($visibility) {
            $result['iam_visible'] = ($visibility === 'yes' || $visibility === 'unknown');

            if ($status === 'user' || $status === 'autor') {
                $result['visible_mode'] = 'participant';
            } else {
                $result['iam_visible'] = true;
                $result['visible_mode'] = false;
            }
        }

        return $result;
    }

    /**
     * Returns the Subject for the Messaging
     * @return String
     */
    private function getSubject()
    {
        $result = Seminar::GetInstance($this->course_id)->getNumber();

        return ($result == '') ? sprintf('[%s]', $this->course_title) :
                sprintf(_('[%s: %s]'), $result, $this->course_title);
    }

    private function createSidebar($filtered_members)
    {
        $sem = Seminar::GetInstance($this->course_id);
        $course = Course::find($this->course_id);

        $sidebar = Sidebar::get();
        $widget  = $sidebar->addWidget(new ActionsWidget());

        if ($this->is_tutor || $this->config->COURSE_STUDENT_MAILING) {
            $widget->addLink(
                _('Rundmail schreiben'),
                URLHelper::getURL('dispatch.php/course/members/circular_mail', [
                    'course_id' => $this->course_id,
                    'default_subject' => $this->subject
                ]),
                Icon::create('mail')
            )->asDialog('size=auto');
        }
        if ($this->is_tutor) {
            //Calculate the course institutes here since they are needed
            //in three different parts of the followint source code.
            //The course institutes are the main institute and the
            //participating institutes.
            $course_institute_ids = [
                $course->home_institut->id
            ];
            foreach ($course->institutes as $inst) {
                if ($inst->id != $course->home_institut->id) {
                    $course_institute_ids[] = $inst->id;
                }
            }
            if ($this->is_dozent) {
                if (!$this->dozent_is_locked) {
                    $sem_institutes = $sem->getInstitutes();

                    if (SeminarCategories::getByTypeId($sem->status)->only_inst_user) {
                        $search_template = 'user_inst_not_already_in_sem';
                    } else {
                        $search_template = 'user_not_already_in_sem';
                    }

                    // create new search for dozent
                    $searchtype = new PermissionSearch(
                        $search_template,
                        sprintf(
                            _('%s suchen'),
                            get_title_for_status('dozent', 1, $sem->status)
                        ),
                        'user_id',
                        [
                            'permission' => 'dozent',
                            'institute'  => $sem_institutes,
                            'seminar_id' => $course->id,
                        ]
                    );

                    // quickfilter: dozents of institut
                    $sql = "SELECT `user_id`
                            FROM `user_inst`
                            WHERE `Institut_id` IN (:institute_ids)
                              AND `inst_perms` = 'dozent'";
                    $db = DBManager::get();
                    $statement = $db->prepare($sql);
                    $statement->execute(['institute_ids' => $course_institute_ids]);
                    $membersOfInstitute = $statement->fetchAll(PDO::FETCH_COLUMN);

                    // add "add dozent" to infobox
                    $mp = MultiPersonSearch::get("add_dozent{$this->course_id}")
                        ->setLinkText(sprintf(_('%s eintragen'), get_title_for_status('dozent', 1, $sem->status)))
                        ->setDefaultSelectedUser($filtered_members['dozent']->pluck('user_id'))
                        ->setLinkIconPath("")
                        ->setTitle(sprintf(_('%s eintragen'), get_title_for_status('dozent', 1, $sem->status)))
                        ->setExecuteURL($this->url_for('course/members/execute_multipersonsearch_dozent'))
                        ->setSearchObject($searchtype)
                        ->addQuickfilter(
                            sprintf(
                                ngettext(
                                    '%s der Einrichtung',
                                    '%s der Einrichtungen',
                                    count($course_institute_ids)
                                ),
                                $this->status_groups['dozent']
                            ),
                            $membersOfInstitute)
                        ->setNavigationItem('/course/members/view')
                        ->render();
                    $element = LinkElement::fromHTML($mp, Icon::create('add'));
                    $widget->addElement($element);
                }
                if (!$this->tutor_is_locked) {
                    $sem_institutes = $sem->getInstitutes();

                    if (SeminarCategories::getByTypeId($sem->status)->only_inst_user) {
                        $search_template = 'user_inst_not_already_in_sem';
                    } else {
                        $search_template = 'user_not_already_in_sem';
                    }

                    // create new search for tutor
                    $searchType = new PermissionSearch(
                        $search_template,
                        sprintf(
                            _('%s suchen'),
                            get_title_for_status('tutor', 1, $sem->status)
                        ),
                        'user_id',
                        [
                            'permission' => ['dozent', 'tutor'],
                            'institute'  => $sem_institutes,
                            'seminar_id' => $course->id,
                        ]
                    );

                    // quickfilter: tutors of institut
                    $sql = "SELECT `user_id`
                            FROM `user_inst`
                            WHERE `Institut_id` IN (:institute_ids)
                              AND `inst_perms` = 'tutor'";
                    $db = DBManager::get();
                    $statement = $db->prepare($sql);
                    $statement->execute(['institute_ids' => $course_institute_ids]);
                    $membersOfInstitute = $statement->fetchAll(PDO::FETCH_COLUMN);

                    // add "add tutor" to infobox
                    $mp = MultiPersonSearch::get("add_tutor{$this->course_id}")
                        ->setLinkText(sprintf(_('%s eintragen'), get_title_for_status('tutor', 1, $sem->status)))
                        ->setDefaultSelectedUser($filtered_members['tutor']->pluck('user_id'))
                        ->setLinkIconPath('')
                        ->setTitle(sprintf(_('%s eintragen'), get_title_for_status('tutor', 1, $sem->status)))
                        ->setExecuteURL($this->url_for('course/members/execute_multipersonsearch_tutor'))
                        ->setSearchObject($searchType)
                        ->addQuickfilter(
                            sprintf(
                                ngettext(
                                    '%s der Einrichtung',
                                    '%s der Einrichtungen',
                                    count($course_institute_ids)
                                ),
                                $this->status_groups['tutor']),
                            $membersOfInstitute)
                        ->setNavigationItem('/course/members/view')
                        ->render();
                    $element = LinkElement::fromHTML($mp, Icon::create('add'));
                    $widget->addElement($element);
                }
            }
            if (!$this->is_locked) {
                // create new search for members

                // The course institutes are the main institute and the
                // participating institutes.
                $course_institute_ids = [$course->home_institut->id];
                foreach ($course->institutes as $inst) {
                    if ($inst->id !== $course->home_institut->id) {
                        $course_institute_ids[] = $inst->id;
                    }
                }

                // create new search for autor
                $searchType = new PermissionSearch(
                    'user_not_already_in_sem',
                    sprintf(
                        _('%s suchen'),
                        get_title_for_status('autor', 1, $sem->status)
                    ),
                    'user_id',
                    [
                        'permission' => ['autor', 'tutor', 'dozent'],
                        'institute'  => $sem_institutes,
                        'seminar_id' => $course->id,
                    ]
                );

                // quickfilter: autors of institut
                $sql = "SELECT `user_id`
                        FROM `user_inst`
                        WHERE `Institut_id` IN (:institute_ids)
                          AND `inst_perms` = 'autor'";
                $db = DBManager::get();
                $statement = $db->prepare($sql);
                $statement->execute(['institute_ids' => $course_institute_ids]);
                $membersOfInstitute = $statement->fetchAll(PDO::FETCH_COLUMN);

                // add "add autor" to infobox
                $mp = MultiPersonSearch::get("add_autor{$this->course_id}")
                    ->setLinkText(sprintf(_('%s eintragen'), get_title_for_status('autor', 1, $sem->status)))
                    ->setDefaultSelectedUser($filtered_members['autor']->pluck('user_id'))
                    ->setLinkIconPath('')
                    ->setTitle(sprintf(_('%s eintragen'), get_title_for_status('autor', 1, $sem->status)))
                    ->setExecuteURL($this->url_for('course/members/execute_multipersonsearch_autor'))
                    ->setSearchObject($searchType)
                    ->addQuickfilter(
                        sprintf(
                            ngettext(
                                '%s der Einrichtung',
                                '%s der Einrichtungen',
                                count($course_institute_ids)
                            ),
                            $this->status_groups['autor']
                        ),
                        $membersOfInstitute
                    )
                    ->setNavigationItem('/course/members/view')
                    ->render();
                $widget->addElement(LinkElement::fromHTML(
                    $mp,
                    Icon::create('add')
                ));

                // add "add person to waitlist" to sidebar
                if (
                    $sem->isAdmissionEnabled()
                    && $sem->getCourseSet()->hasAlgorithmRun()
                    && !$sem->admission_disable_waitlist
                    && (!$sem->getFreeSeats() || $sem->admission_disable_waitlist_move)
                ) {
                    $ignore = array_merge(
                        $filtered_members['dozent']->pluck('user_id'),
                        $filtered_members['tutor']->pluck('user_id'),
                        $filtered_members['autor']->pluck('user_id'),
                        $filtered_members['user']->pluck('user_id'),
                        $filtered_members['awaiting']->pluck('user_id')
                    );
                    $mp = MultiPersonSearch::get("add_waitlist{$this->course_id}")
                        ->setLinkText(_('Person(en) auf Warteliste eintragen'))
                        ->setDefaultSelectedUser($ignore)
                        ->setLinkIconPath('')
                        ->setTitle(_('Person(en) auf Warteliste eintragen'))
                        ->setExecuteURL($this->url_for('course/members/execute_multipersonsearch_waitlist'))
                        ->setSearchObject($searchType)
                        ->addQuickfilter(_('Mitglieder der Einrichtung'), $membersOfInstitute)
                        ->setNavigationItem('/course/members/view')
                        ->render();
                    $element = LinkElement::fromHTML($mp, Icon::create('add'));
                    $widget->addElement($element);
                }
                $widget->addLink(
                    _('Teilnehmendenliste importieren'),
                    $this->import_autorlistURL(),
                    Icon::create('persons'),
                    ['data-dialog' => 1]
                );

            }

            if (Config::get()->EXPORT_ENABLE) {
                $widget = $sidebar->addWidget(new ExportWidget());

                // create csv-export link
                $csvExport = export_link(
                    $this->course_id,
                    'person',
                    sprintf('%s %s', $this->status_groups['autor'], $this->course_title),
                    'csv',
                    'csv-teiln',
                    '',
                    _('Liste als csv-Dokument exportieren'),
                    'passthrough'
                );
                $widget->addLinkFromHTML(
                    $csvExport,
                    Icon::create('export')
                );

                // create csv-export link
                $rtfExport = export_link(
                    $this->course_id,
                    'person',
                    sprintf('%s %s', $this->status_groups['autor'], $this->course_title),
                    'rtf',
                    'rtf-teiln',
                    '',
                    _('Liste als rtf-Dokument exportieren'),
                    'passthrough'
                );
                $widget->addLinkFromHTML(
                    $rtfExport,
                    Icon::create('export')
                );

                if (count($this->awaiting) > 0) {
                    $awaiting_rtf = export_link(
                        $this->course_id,
                        'person',
                        sprintf(_('Warteliste %s'), $this->course_title),
                        'rtf',
                        'rtf-warteliste',
                        $this->waiting_type,
                        _('Warteliste als rtf-Dokument exportieren'),
                        'passthrough'
                    );
                    $widget->addLinkFromHTML(
                        $awaiting_rtf,
                        Icon::create('export')
                    );

                    $awaiting_csv = export_link(
                        $this->course_id,
                        'person',
                        sprintf(_('Warteliste %s'), $this->course_title),
                        'csv',
                        'csv-warteliste',
                        $this->waiting_type,
                        _('Warteliste als csv-Dokument exportieren'),
                        'passthrough'
                    );
                    $widget->addLinkFromHTML(
                        $awaiting_csv,
                        Icon::create('export')
                    );
                }
            }

            $options = new OptionsWidget();
            $options->addCheckbox(
                _('Diese Seite für Studierende verbergen'),
                $this->config->COURSE_MEMBERS_HIDE,
                $this->url_for('course/members/course_members_hide/1'),
                $this->url_for('course/members/course_members_hide/0'),
                ['title' => _('Über diese Option können Sie die Teilnehmendenliste für Studierende der Veranstaltung unsichtbar machen')]
            );

            if ($this->is_dozent) {
                $options->addCheckbox(
                    _('Rundmails von Studierenden erlauben'),
                    $this->config->COURSE_STUDENT_MAILING,
                    $this->url_for('course/members/toggle_student_mailing/1'),
                    $this->url_for('course/members/toggle_student_mailing/0'),
                    ['title' => _('Über diese Option können Sie Studierenden das Schreiben von Nachrichten an alle anderen Teilnehmenden der Veranstaltung erlauben')]
                );
            }
            $sidebar->addWidget($options);
        } else if ($this->is_autor || $this->is_user) {
            // Visibility preferences
            if ($this->my_visibility['iam_visible']) {
                $text = _('Sie sind für andere Teilnehmenden auf der Teilnehmendenliste sichtbar.');
                $icon = Icon::create('visibility-invisible');
                $modus = 'make_invisible';
                $link_text = _('Klicken Sie hier, um unsichtbar zu werden.');
            } else {
                $text = _('Sie sind für andere Teilnehmenden auf der Teilnehmendenliste nicht sichtbar.');
                $icon = Icon::create('visibility-visible');
                $modus = 'make_visible';
                $link_text = _('Klicken Sie hier, um sichtbar zu werden.');
            }

            $actions = $sidebar->addWidget(new ActionsWidget());
            $actions->addLink(
                $link_text,
                $this->change_visibilityURL($modus, $this->my_visibility['visible_mode']),
                $icon,
                ['title' => $text]
            );
        }
    }

    public function export_members_csv_action()
    {
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }
        $filtered_members = CourseMember::getMembers($this->sort_status, $this->sort_by . ' ' . $this->order);
        $filtered_members = array_merge(
            $filtered_members,
            AdmissionApplication::getAdmissionMembers(
                $this->course_id,
                $this->sort_status,
                $this->sort_by . ' ' . $this->order )
        );
        $dozenten = $filtered_members['dozent']->toArray('user_id username vorname nachname visible mkdate');
        $tutoren = $filtered_members['tutor']->toArray('user_id username vorname nachname visible mkdate');
        $autoren = $filtered_members['autor']->toArray('user_id username vorname nachname visible mkdate');

        $header = [_('Titel'), _('Vorname'), _('Nachname'), _('Titel2'), _('Nutzernamen'), _('Privatadr'), _('Privatnr'), _('E-Mail'), _('Anmeldedatum'), _('Studiengänge')];
        $data = [$header];
        foreach ([$dozenten, $tutoren, $autoren] as $usergroup) {
            foreach ($usergroup as $dozent) {
                $line = [
                    '',
                    $dozent['Vorname'],
                    $dozent['Nachname'],
                    '',
                    $dozent['username']
                ];
                $data[] = $line;
            }
        }
        $csv = array_to_csv($data);
    }

    public function toggle_student_mailing_action($state)
    {
        if (!$this->is_dozent) {
            throw new AccessDeniedException();
        }

        $this->config->store('COURSE_STUDENT_MAILING', $state);

        $this->redirect($this->indexURL());
    }

    public function course_members_hide_action($state)
    {
        if (!$this->is_tutor) {
            throw new AccessDeniedException();
        }

        $this->config->store('COURSE_MEMBERS_HIDE', $state);

        $this->redirect($this->indexURL());
    }


    public function circular_mail_action()
    {
        if (!$this->is_tutor && !$this->config->COURSE_STUDENT_MAILING) {
            throw new AccessDeniedException();
        }

        //Calculate the amount of recipients for each group:
        $this->user_count = CourseMember::countByCourseAndStatus($this->course_id, 'user');
        $this->autor_count = CourseMember::countByCourseAndStatus($this->course_id, 'autor');
        $this->tutor_count = CourseMember::countByCourseAndStatus($this->course_id, 'tutor');
        $this->dozent_count = CourseMember::countByCourseAndStatus($this->course_id, 'dozent');

        //Use the correct names for thte four status groups:
        $sem = Seminar::GetInstance($this->course_id);
        $this->user_name = get_title_for_status('user', 0, $sem->status);
        $this->autor_name = get_title_for_status('autor', 0, $sem->status);
        $this->tutor_name = get_title_for_status('tutor', 0, $sem->status);
        $this->dozent_name = get_title_for_status('dozent', 0, $sem->status);

        $this->default_subject = Request::get('default_subject');

        if ($this->is_tutor) {
            $this->awaiting_count = AdmissionApplication::countBySql(
                "seminar_id = :course_id AND status = 'awaiting'",
                [
                    'course_id' => $this->course_id
                ]
            );
            $this->accepted_count = AdmissionApplication::countBySql(
                "seminar_id = :course_id AND status = 'accepted'",
                [
                    'course_id' => $this->course_id
                ]
            );
        }
        $this->default_selected_groups = ['dozent', 'tutor', 'autor', 'user'];
        $this->all_available_groups = $this->default_selected_groups;
        if ($this->is_tutor) {
            //The user has at least tutor permissions:
            if ($this->accepted_count) {
                $this->all_available_groups[] = 'accepted';
            }
            if ($this->awaiting_count) {
                $this->all_available_groups[] = 'awaiting';
            }
        }
        if (Request::submitted('write')) {
            CSRFProtection::verifyUnsafeRequest();

            $this->selected_groups = Request::getArray('selected_groups');
            //Filter all selected groups by the list of all available groups:
            $filtered_groups = [];
            foreach ($this->selected_groups as $group) {
                if (in_array($group, $this->all_available_groups)) {
                    $filtered_groups[] = $group;
                }
            }
            if ($filtered_groups == $this->default_selected_groups) {
                $this->redirect(URLHelper::getURL(
                    'dispatch.php/messages/write',
                    [
                        'course_id' => $this->course_id,
                        'default_subject' => $this->default_subject,
                        'filter' => 'all',
                        'emailrequest' => 1
                    ]
                ));
            } elseif ($filtered_groups == $this->all_available_groups) {
                $this->redirect(URLHelper::getURL(
                    'dispatch.php/messages/write',
                    [
                        'course_id' => $this->course_id,
                        'default_subject' => $this->default_subject,
                        'filter' => 'really_all',
                        'emailrequest' => 1
                    ]
                ));
            } else {
                //Do custom filtering.
                $filters = [];
                $who_param = [];

                foreach ($filtered_groups as $group) {
                    if ($group === 'awaiting') {
                        $filters[] = 'awaiting';
                    } elseif ($group === 'accepted') {
                        $filters[] = 'prelim';
                    } elseif ($group === 'user') {
                        $filters[] = 'all';
                        $who_param[] = 'user';
                    } elseif ($group === 'autor') {
                        $filters[] = 'all';
                        $who_param[] = 'autor';
                    } elseif ($group === 'tutor') {
                        $filters[] = 'all';
                        $who_param[] = 'tutor';
                    } elseif ($group === 'dozent') {
                        $filters[] = 'all';
                        $who_param[] = 'dozent';
                    }
                }
                $filters = array_unique($filters);
                if (!$filters) {
                    PageLayout::postError(
                        _('Es wurde keine Gruppe ausgewählt!')
                    );
                    return;
                }

                $url_params = [
                    'course_id' => $this->course_id,
                    'default_subject' => $this->default_subject,
                    'filter' => implode(',', array_unique($filters)),
                    'emailrequest' => 1
                ];
                if ($who_param) {
                    $url_params['who'] = implode(',', $who_param);
                }

                $this->redirect(URLHelper::getURL(
                    'dispatch.php/messages/write',
                    $url_params
                ));
            }
        }
    }
    public function checkUserVisibility()
    {
        $membership = CourseMember::findOneBySQL("visible = 'unknown' AND Seminar_id = ?", [$this->course_id]);
        if ($membership) {
            CourseMember::findEachBySQL(
                function(CourseMember $membership) {
                    $membership->visible = 'yes';
                    $membership->store();
                },
                "status IN ('tutor', 'dozent') AND Seminar_id = ?",
                [$this->course_id]
            );

            CourseMember::findEachBySQL(
                function(CourseMember $membership) {
                    $user = $membership->user;
                    if (in_array($user->visible, ['no','never'])
                        || ($user->visible === 'unknown') && (int)!Config::get()->USER_VISIBILITY_UNKNOWN
                    ) {
                        $mode = 'no';
                    } else {
                        $mode = 'yes';
                    }
                    $membership->visible = $mode;
                    $membership->store();
                },
                "Seminar_id = ? AND visible='unknown'",
                [$this->course_id]
            );
        }
    }

    private function setMemberStatus($members, $status, $next_status, $direction)
    {
        $msgs = [];
        foreach ($members as $user_id) {
            $temp_user = User::find($user_id);
            if ($next_status == 'tutor' && !$GLOBALS['perm']->have_perm('tutor', $user_id)) {
                $msgs['no_tutor'][$user_id] = $temp_user->getFullName();
            } else {
                if ($temp_user) {
                    $next_pos = 0;
                    // get the next position of the user
                    switch ($next_status) {
                        case 'autor':
                        case 'user':
                            // get the current position of the user
                            $next_pos = $this->getPosition($user_id);
                            break;
                        // set the status to tutor
                        case 'tutor':
                            // get the next position of the user
                            $next_pos = CourseMember::getNextPosition($next_status, $this->course_id);
                            // resort the tutors
                            CourseMember::resortMembership($this->course_id, $this->getPosition($user_id));
                            break;
                    }

                    $membership = CourseMember::findOneBySQL(
                        'Seminar_id = ? AND user_id = ? AND status = ?',
                        [$this->course_id, $user_id, $status]
                    );
                    $membership->status = $next_status;
                    $membership->position = $next_pos;

                    if ($membership->store()) {
                        StudipLog::log('SEM_CHANGED_RIGHTS', $this->course_id, $user_id, $next_status,
                            $this->getLogLevel($direction, $next_status));
                        NotificationCenter::postNotification('CourseMemberStatusDidUpdate', $this->course_id, $user_id);
                        if ($next_status === 'autor') {
                            CourseMember::resortMembership($this->course_id, $next_pos);
                        }
                        $msgs['success'][$user_id] = $temp_user->getFullName();
                    }
                }
            }
        }

        if (!empty($msgs)) {
            return $msgs;
        } else {
            return false;
        }
    }

    public function addMember(string $user_id, bool $accepted = false, bool $consider_contingent = null, &$msg = []): bool
    {
        $user = User::find($user_id);
        $messaging = new messaging;

        $status = 'autor';
        $msg = [];
        // insert
        $copy_course = $accepted || $consider_contingent;
        $admission_user = CourseMember::insertCourseMember($this->course_id, $user_id, $status, $copy_course, $consider_contingent, true);

        if ($admission_user) {
            setTempLanguage($user_id);
            $message = sprintf(
                _('Sie wurden in die Veranstaltung **%s** eingetragen.'),
                $this->course_title
            );
            restoreLanguage();
            $messaging->insert_message(
                $message,
                $user->username,
                '____%system%____',
                false,
                false,
                '1',
                false,
                sprintf('%s %s', _('Systemnachricht:'), _('Eintragung in Veranstaltung')),
                true
            );
            $msg['success'] = sprintf(
                _('%1$s wurde in die Veranstaltung mit dem Status<b>%2$s</b> eingetragen.'),
                $user->getFullName(),
                $status
            );
        } else if ($consider_contingent) {
            PageLayout::postError(_('Es stehen keine weiteren Plätze mehr im Teilnehmendenkontingent zur Verfügung.'));
            return false;
        } else {
            PageLayout::postError(
                _('Beim Eintragen ist ein Fehler aufgetreten. Bitte versuchen Sie es erneut oder wenden Sie sich an die Administrierenden')
            );
            return false;
        }
        //Warteliste neu sortieren
        AdmissionApplication::renumberAdmission($this->course_id);
        return true;
    }

    /**
     * Adds the given user to the waitlist of the current course and sends a
     * corresponding message.
     *
     * @param String $user_id The user to add
     * @return bool Successful operation?
     */
    private function addToWaitlist(string $user_id): bool
    {
        $course = Seminar::getInstance($this->course_id);
        // Insert user in waitlist at current position.
        if ($course->addToWaitlist($user_id, 'last')) {
            setTempLanguage($user_id);
            $message = sprintf(_('Sie wurden von einem/einer Veranstaltungsleiter/-in (%1$s) ' .
                'oder einem/einer Administrator/-in auf die Warteliste der Veranstaltung **%2$s** gesetzt.'),
                get_title_for_status('dozent', 1), $this->course_title);
            restoreLanguage();
            messaging::sendSystemMessage($user_id, sprintf('%s %s', _('Systemnachricht:'),
                _('Auf Warteliste gesetzt')), $message);

            return true;
        }
        return false;
    }

    /**
     * Adds the given users to the target course.
     * @param array $users users to add
     * @param string $target_course_id which course to add users to
     * @param bool $move move users (=delete in source course) or just add to target course?
     * @return array success and failure statuses
     */
    private function sendToCourse(array $users, string $target_course_id, bool $move = false): array
    {
        $msg = [];
        foreach ($users as $user) {
            if (!CourseMember::exists([$target_course_id, $user])) {
                $target_course = Seminar::GetInstance($target_course_id);
                if ($target_course->addMember($user)) {
                    if ($move) {
                        $remove_from = Seminar::getInstance($this->course_id);
                        $remove_from->deleteMember($user);
                    }
                    $msg['success'][] = $user;
                } else {
                    $msg['failed'][] = $user;
                }
            } else {
                $msg['existing'][] = $user;
            }
        }
        return $msg;
    }

    private function insertAdmissionMember(array $users, string $next_status, bool $consider_contingent, bool $accepted = false, string $cmd = 'add_user'): array
    {
        $messaging = new messaging;
        $status_title = get_title_for_status('dozent', 1);
        foreach ($users as $user_id => $value) {
            if ($value) {
                $user = User::find($user_id);
                if ($user) {
                    $admission_user = CourseMember::insertCourseMember(
                        $this->course_id,
                        $user_id,
                        $next_status,
                        $accepted || $consider_contingent,
                        $consider_contingent
                    );

                    // only if user was on the waiting list
                    if ($admission_user) {
                        setTempLanguage($user_id);
                        restoreLanguage();

                        if ($cmd === 'add_user') {
                            $message = sprintf(
                                _('Sie wurden in die Veranstaltung **%s** eingetragen.'),
                                $this->course_title
                            );
                        } else {
                            if (!$accepted) {
                                $message = sprintf(_('Sie wurden aus der Warteliste in die Veranstaltung **%s** aufgenommen und sind damit zugelassen.'),
$this->course_title);
                            } else {
                                $message = sprintf(_('Sie wurden vom Status **vorläufig akzeptiert** auf **teilnehmend** in der Veranstaltung **%s** hochgestuft und sind damit zugelassen.'), $this->course_title);
                            }
                        }

                        $messaging->insert_message(
                            $message,
                            $user->username,
                            '____%system%____',
                            false,
                            false,
                            '1',
                            false,
                            sprintf('%s %s', _('Systemnachricht:'), _('Eintragung in Veranstaltung')),
                            true
                        );
                        $msgs[] = $user->getFullName();
                    }
                }
            }
        }

        // resort admissionlist
        AdmissionApplication::renumberAdmission($this->course_id);

        return $msgs;
    }

    /**
     * Adds given users to the course waitlist, either at list beginning or end.
     * System messages are sent to affected users.
     *
     * @param array $users array of user ids to add
     * @param String $which_end 'last' or 'first': which list end to append to
     * @return array Array of messages (stating success and/or errors)
     */
    public function moveToWaitlist($users, $which_end)
    {
        $course = Seminar::getInstance($this->course_id);
        $msgs = [];
        foreach ($users as $user_id) {
            // Delete member from seminar
            $temp_user = User::find($user_id);
            if ($course->deleteMember($user_id)) {
                setTempLanguage($user_id);
                $message = sprintf(_('Sie wurden aus der Veranstaltung **%s** abgemeldet. '.
                    'Sie wurden auf die Warteliste dieser Veranstaltung gesetzt.'),
                    $this->course_title);
                restoreLanguage();
                messaging::sendSystemMessage($user_id, sprintf('%s %s', _('Systemnachricht:'),
                    _('Anmeldung aufgehoben, auf Warteliste gesetzt')), $message);
                if ($course->addToWaitlist($user_id, $which_end)) {
                    $msgs['success'][] = $temp_user->getFullname('no_title');
                } else {
                    $msgs['error'][] = $temp_user->getFullname('no_title');
                }
                // Something went wrong on inserting the user in waitlist.
            } else {
                $msgs['error'][] = $temp_user->getFullname('no_title');
            }
        }
        return $msgs;
    }

    /**
     * Get the position out of the database
     * @param String $user_id
     * @return int
     */
    private function getPosition($user_id): ?int
    {
        $membership = CourseMember::findByUser($user_id);
        if ($membership) {
            return (int)$membership->position;
        }
        return 0;
    }

    private function getLogLevel($direction, $status)
    {
        if ($direction === 'upgrade') {
            $directionString = 'hochgestuft';
        } else {
            $directionString = 'runtergestuft';
        }

        switch ($status) {
            case 'tutor': $log_level = 'zum Tutor';
                break;
            case 'autor': $log_level = 'zum Autor';
                break;
            case 'dozent': $log_level = 'zum Dozenten';
                break;
        }

        return sprintf('%s %s', $directionString, $log_level);
    }
}
