<?php
/**
 * formerly admin_institut.php - Grunddaten fuer ein Institut
 *
 * @author  Arne Schröder <schroeder@data-quest.de>
 * @author  Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author  Cornelis Kater <ckater@gwdg.de>
 * @author  Stefan Suchi <suchi@gmx.de>
 * @license GPL2 or any version
 * @since   Stud.IP 3.3
 */

class Institute_BasicdataController extends AuthenticatedController
{
    /**
     * common tasks for all actions
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // Ensure only admins gain access to this page
        if (!$GLOBALS['perm']->have_perm("admin")) {
            throw new AccessDeniedException();
        }
    }

    /**
     * show institute basicdata page
     *
     * @param mixed $i_id Optional institute id
     * @throws AccessDeniedException
     */
    public function index_action($i_id = false)
    {
        PageLayout::setTitle(_('Verwaltung der Grunddaten'));

        PageLayout::addSqueezePackage('avatar');

        //get ID from an open Institut
        $i_view = $i_id ?: Request::option('i_view', Context::getId());

        if (!$i_view) {
            Navigation::activateItem('/admin/institute/details');
            require_once 'lib/admin_search.inc.php';

            // This search just died a little inside, so it should be safe to
            // continue here but we nevertheless return just to be sure
            return;
        } elseif ($i_view === 'new') {
            closeObject();
            Navigation::activateItem('/admin/institute/create');
        } else {
            Navigation::activateItem('/admin/institute/details');
        }

        //  allow only inst-admin and root to view / edit
        if ($i_view && !$GLOBALS['perm']->have_studip_perm('admin', $i_view) && $i_view !== 'new') {
            throw new AccessDeniedException();
        }

        //Change header_line if open object
        $header_line = null;
        if (Context::get())
        {
            $header_line = Context::getHeaderLine();
        }
        if ($header_line) {
            PageLayout::setTitle($header_line . ' - ' . PageLayout::getTitle());
        }

        if (Request::get('i_trykill')) {
            $message              = _('Sind Sie sicher, dass Sie diese Einrichtung löschen wollen?');
            $post['i_kill']       = 1;
            $post['studipticket'] = get_ticket();
            $this->question = (string) QuestionBox::create(
                $message,
                $this->url_for('institute/basicdata/delete/' . $i_view, $post),
                $this->url_for('institute/basicdata/index/' . $i_view)
            );
        }

        $lockrule = LockRules::getObjectRule($i_view);
        if ($lockrule && $lockrule->description && LockRules::CheckLockRulePermission($i_view, $lockrule['permission'])) {
            PageLayout::postInfo(formatLinks($lockrule->description));
        }

        // Load institute data
        $institute = new Institute($i_view === 'new' ? null : $i_view);

        //add the free administrable datafields
        $datafields = [];
        $localEntries = DataFieldEntry::getDataFieldEntries($institute->id, 'inst');
        if ($localEntries) {
            $invalidEntries = $this->flash['invalid_entries'] ?: [];
            foreach ($localEntries as $entry) {
                if (!$entry->isVisible()) {
                    continue;
                }

                $color = '#000000';
                if (in_array($entry->getId(), $invalidEntries)) {
                    $color = '#ff0000';
                }
                $datafields[] = [
                    'color' => $color,
                    'value' => ($GLOBALS['perm']->have_perm($entry->isEditable())
                                && !LockRules::Check($institute['Institut_id'], $entry->getId()))
                             ? $entry->getHTML('datafields')
                             : $entry->getDisplayValue(),
                ];
            }
        }

        // Read faculties if neccessary
        if (count($institute->sub_institutes) === 0) {
            if ($GLOBALS['perm']->have_perm('root')) {
                $this->faculties = Institute::findBySQL('Institut_id = fakultaets_id ORDER BY Name ASC');
            } else {
                $temp = User::find($GLOBALS['user']->id)
                            ->institute_memberships->findBy('inst_perms', 'admin')
                            ->pluck('institute');
                $institutes = SimpleORMapCollection::createFromArray($temp);
                $faculties  = $institutes->filter(function ($institute) {
                    return $institute->is_fak;
                });
                $this->faculties = $faculties;
            }
        }
        $reason_txt = '';
        // Indicates whether the current user is allowed to delete the institute
        $this->may_delete = $i_view !== 'new'
                         && !(count($institute->home_courses) || count($institute->sub_institutes))
                         && ($GLOBALS['perm']->have_perm('root')
                             || ($GLOBALS['perm']->is_fak_admin() && Config::get()->INST_FAK_ADMIN_PERMS == 'all'));
        if (!$this->may_delete) {
            //Set infotext for disabled delete-button
            $reason_txt = _('Löschen nicht möglich.');
            if (count($institute->home_courses) > 0) {
                $reason_txt .= ' ';
                $reason_txt .= sprintf(ngettext('Es ist eine Veranstaltung zugeordnet.',
                                                'Es sind %u Veranstaltungen zugeordnet.',
                                                count($institute->home_courses)),
                                       count($institute->home_courses));
            }
            if (count($institute->sub_institutes) > 0) {
                $reason_txt .= ' ';
                $reason_txt .= sprintf(ngettext('Es ist eine Einrichtung zugeordnet.',
                                                'Es sind %u Einrichtungen zugeordnet.',
                                                count($institute->sub_institutes)),
                                       count($institute->sub_institutes));
            }
        }
        // Indicates whether the current user is allowed to change the faculty
        $this->may_edit_faculty = $GLOBALS['perm']->is_fak_admin()
                               && !LockRules::Check($institute['Institut_id'], 'fakultaets_id')
                               && ($GLOBALS['perm']->have_studip_perm('admin', $institute['fakultaets_id']) || $i_view === 'new');

        // Prepare template
        $this->institute      = $institute;
        $this->i_view         = $i_view;
        $this->datafields     = $datafields;
        $this->reason_txt     = $reason_txt;
    }

    /**
     * Stores the changed or created institute data
     *
     * @param String $i_id Institute id or 'new' to create
     * @throws MethodNotAllowedException
     */
    public function store_action($i_id)
    {
        // We won't accept anything but a POST request
        if (!Request::isPost()) {
            throw new MethodNotAllowedException();
        }

        $create_institute = $i_id === 'new';

        $institute = new Institute($create_institute ? null : $i_id);
        $institute->name            = Request::i18n('Name', $institute->name)->trim();
        $institute->fakultaets_id   = Request::option('Fakultaet', $institute->fakultaets_id);
        $institute->strasse         = Request::get('strasse', $institute->strasse);
        // Beware: Despite the name, this contains both zip code AND city name
        $institute->plz             = Request::get('plz', $institute->plz);
        $institute->url             = Request::i18n('url', $institute->url)->trim();
        $institute->telefon         = Request::get('telefon', $institute->telefon);
        $institute->email           = Request::get('email', $institute->email);
        $institute->fax             = Request::get('fax', $institute->fax);
        $institute->type            = Request::int('type', $institute->type);
        $institute->lit_plugin_name = Request::get('lit_plugin_name', $institute->lit_plugin_name);
        $institute->lock_rule       = Request::option('lock_rule', $institute->lock_rule);

        // Do we have all necessary data?
        if (!mb_strlen($institute->name)) {
            PageLayout::postError(_('Bitte geben Sie eine Bezeichnung für die Einrichtung ein!'));
            $this->redirect('institute/basicdata/index/' . $i_id);
            return;
        }

        if ($create_institute) {
            $institute->id = $institute->getNewId();

            // Is the user allowed to create new faculties
            if (!$institute->fakultaets_id && !$GLOBALS['perm']->have_perm('root')) {
                PageLayout::postError(_('Sie haben nicht die Berechtigung, neue Fakultäten zu erstellen'));
                $this->redirect('institute/basicdata/index/new');
                return;
            }

            // Is the user allowed to create new institutes
            if (!$GLOBALS['perm']->have_perm('root') && !($GLOBALS['perm']->is_fak_admin() && Config::get()->INST_FAK_ADMIN_PERMS !== 'none'))  {
                PageLayout::postError(_('Sie haben nicht die Berechtigung, um neue Einrichtungen zu erstellen!'));
                $this->redirect('institute/basicdata/index/new');
                return;
            }

            // Does an institute with the given name already exist in the given faculty?
            if ($institute->fakultaets_id && Institute::findOneBySQL('Name = ? AND fakultaets_id = ?', [$institute->name, $institute->fakultaets_id]) !== null) {
                PageLayout::postError(sprintf(
                    _('Die Einrichtung "%s" existiert bereits innerhalb der angegebenen Fakultät!'),
                    htmlReady($institute->name)
                ));
                $this->redirect('institute/basicdata/index/new');
                return;
            }

            // Does a faculty with the given name already exist
            if (!$institute->fakultaets_id && Institute::findOneBySQL('Name = ? AND fakultaets_id = Institut_id', [$institute->name]) !== null) {
                PageLayout::postError(sprintf(_('Die Fakultät "%s" existiert bereits!'), htmlReady($institute->name)));
                $this->redirect('institute/basicdata/index/new');
                return;
            }


            // Declare faculty status if neccessary
            if (!$institute->fakultaets_id) {
                $institute->fakultaets_id = $institute->getId();
            }
        } else {
            // Is the user allowed to change the institute/faculty?
            if (!$GLOBALS['perm']->have_studip_perm('admin', $institute->id)) {
                PageLayout::postError(_('Sie haben nicht die Berechtigung diese Einrichtung zu verändern!'));
                $this->redirect('institute/basicdata/index/' . $institute->id);
                return;
            }

            // Save datafields
            $datafields = Request::getArray('datafields');
            $invalidEntries = [];
            $datafields_stored = 0;
            foreach (DataFieldEntry::getDataFieldEntries($institute->id, 'inst') as $entry) {
                if (isset($datafields[$entry->getId()])) {
                    $valueBefore = $entry->getValue();
                    $entry->setValueFromSubmit($datafields[$entry->getId()]);
                    if ($valueBefore != $entry->getValue()) {
                        if ($entry->isValid()) {
                            $datafields_stored += 1;
                            $entry->store();
                        } else {
                            $invalidEntries[] = $entry->getId();
                        }
                    }
                }
            }

            // If any errors occured while updating the datafields, report them
            if (count($invalidEntries) > 0) {
                $this->flash['invalid_entries'] = $invalidEntries;
                PageLayout::postError(_('ungültige Eingaben (s.u.) wurden nicht gespeichert'));
            }
        }

        // Try to store the institute, report any errors
        if ($institute->isDirty() && $institute->store() === false) {
            if ($institute->isNew()) {
                PageLayout::postError(_('Die Einrichtung konnte nicht angelegt werden.'));
            } else {
                PageLayout::postError(_('Die Änderungen konnten nicht gespeichert werden.'));
            }
            $this->redirect('institute/basicdata/index/' . $i_id);
            return;
        }

        if ($create_institute) {
            // Log creation of institute
            StudipLog::log('INST_CREATE', $institute->id, null, null, ''); // logging

            PageLayout::postSuccess(sprintf(
                _('Die Einrichtung "%s" wurde erfolgreich angelegt.'),
                htmlReady($institute->name))
            );

            object_set_visit($institute->id, 0);
        } else {
            PageLayout::postSuccess(sprintf(
                _('Die Änderung der Einrichtung "%s" wurde erfolgreich gespeichert.'),
                htmlReady($institute->name))
            );
        }

        $this->redirect('institute/basicdata/index/' . $institute->id, ['cid' => $institute->id]);
    }

    /**
     * Deletes an institute
     * @param String $i_id Institute id
     */
    public function delete_action($i_id)
    {
        CSRFProtection::verifyUnsafeRequest();

        // Missing parameter
        if (!Request::get('i_kill')) {
            $this->redirect('institute/basicdata/index/' . $i_id);
            return;
        }

        // Invalid ticket
        if (!check_ticket(Request::option('studipticket'))) {
            PageLayout::postError(_('Ihr Ticket ist abgelaufen. Versuchen Sie die letzte Aktion erneut.'));
            $this->redirect('institute/basicdata/index/' . $i_id);
            return;
        }

        // User may not delete this institue
        if (!$GLOBALS['perm']->have_perm('root') && !($GLOBALS['perm']->is_fak_admin() && Config::get()->INST_FAK_ADMIN_PERMS === 'all')) {
            PageLayout::postError(_('Sie haben nicht die Berechtigung Fakultäten zu löschen!'));
            $this->redirect('institute/basicdata/index/' . $i_id);
            return;
        }

        $institute = Institute::find($i_id);
        if ($institute === null) {
            throw new Exception('Invalid institute id');
        }

        // Institut in use?
        if (count($institute->home_courses)) {
            PageLayout::postError(
                _('Diese Einrichtung kann nicht gelöscht werden, da noch Veranstaltungen an dieser Einrichtung existieren!')
            );
            $this->redirect('institute/basicdata/index/' . $i_id);
            return;
        }

        // Institute has sub institutes?
        if (count($institute->sub_institutes)) {
            PageLayout::postError(
                _('Diese Einrichtung kann nicht gelöscht werden, da sie den Status Fakultät hat und noch andere Einrichtungen zugeordnet sind!')
            );
            $this->redirect('institute/basicdata/index/' . $i_id);
            return;
        }

        // Is the user allowed to delete faculties?
        if ($institute->is_fak && !$GLOBALS['perm']->have_perm('root')) {
            PageLayout::postError(_('Sie haben nicht die Berechtigung Fakultäten zu löschen!'));
            $this->redirect('institute/basicdata/index/' . $i_id);
            return;
        }

        // Save users, name and number of courses
        $user_ids  = $institute->members->pluck('user_id');
        $i_name    = $institute->name;
        $i_courses = count($institute->courses);

        // Delete that institute
        if (!$institute->delete()) {
            PageLayout::postError(_('Die Einrichtung konnte nicht gelöscht werden.'));
        } else {
            $details = [];

            // logging - put institute's name in info - it's no longer derivable from id afterwards
            StudipLog::log('INST_DEL', $i_id, NULL, $i_name);

            // set a suitable default institute for each user
            foreach ($user_ids as $user_id) {
                StudipLog::log('INST_USER_DEL', $i_id, $user_id);
                InstituteMember::ensureDefaultInstituteForUser($user_id);
            }
            if (count($user_ids)) {
                $details[] = sprintf(_('%u Mitarbeiter gelöscht.'), count($user_ids));
            }

            // Report number of formerly associated courses
            if ($i_courses) {
                $details[] = sprintf(_('%u Beteiligungen an Veranstaltungen gelöscht'), $i_courses);
            }

            // delete news-links
            StudipNews::DeleteNewsRanges($i_id);

            //delete entry in news_rss_range
            StudipNews::UnsetRssId($i_id);

            //updating range_tree
            $query = "UPDATE range_tree SET name = ?, studip_object = '', studip_object_id = '' WHERE studip_object_id = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                _('(in Stud.IP gelöscht)'),
                $i_id,
            ]);
            if (($db_ar = $statement->rowCount()) > 0) {
                $details[] = sprintf(_('%u Bereiche im Einrichtungsbaum angepasst.'), $db_ar);
            }

            // Statusgruppen entfernen
            if ($db_ar = Statusgruppen::deleteBySQL('range_id = ?', [$i_id]) > 0) {
                $details[] = sprintf(_('%s Funktionen/Gruppen gelöscht.'), $db_ar);
            }

            //kill the datafields
            DataFieldEntry::removeAll($i_id);

            //kill all wiki-pages
            $removed_wiki_pages = 0;
            foreach (['', '_links', '_locks'] as $area) {
                $query = "DELETE FROM wiki{$area} WHERE range_id = ?";
                $statement = DBManager::get()->prepare($query);
                $statement->execute([$i_id]);
                $removed_wiki_pages += $statement->rowCount();
            }
            if ($removed_wiki_pages > 0) {
                $details[] = sprintf(_('%u Wikiseiten gelöscht.'));
            }

            // delete all configuration files for the "extern modules"
            if (Config::get()->EXTERN_ENABLE) {
                $counts = ExternConfig::DeleteAllConfigurations($i_id);
                if ($counts) {
                    $details[] = sprintf(_('%u Konfigurationsdateien für externe Seiten gelöscht.'), $counts);
                }
            }

            // delete all contents in forum-modules
            foreach (PluginEngine::getPlugins('ForumModule') as $plugin) {
                $plugin->deleteContents($i_id);  // delete content irrespective of plugin-activation in the seminar
                if ($plugin->isActivated($i_id)) {   // only show a message, if the plugin is activated, to not confuse the user
                    $details[] = sprintf(_('Einträge in %s gelöscht.'), $plugin->getPluginName());
                }
            }

            // Alle Pluginzuordnungen entfernen
            PluginManager::getInstance()->deactivateAllPluginsForRange('inst', $i_id);

            //Delete all documents of the institute:

            //We must use findOneBySql since findTopFolder cannot determine if
            //the folder exists. The institute has already been deleted and
            //therefore the method findRangeTypeById (called in findTopFolder)
            //cannot determine the range, causing findTopFolder to return null.
            $top_folder = Folder::findOneBySql(
                "range_id = :range_id AND parent_id = ''",
                [
                    'range_id' => $i_id
                ]
            );

            $file_result = false;
            if ($top_folder) {
                //The top folder for the deleted institute exists:
                //We can safely delete it (and its content):
                $file_result = $top_folder->delete();
            }

            if ($file_result) {
                $details[] = _('Alle Dokumente gelöscht.');
            }

            //kill the object_user_vists for this institut
            object_kill_visits(null, $i_id);

            PageLayout::postSuccess(
                sprintf(_('Die Einrichtung "%s" wurde gelöscht!'), htmlReady($i_name)),
                $details
            );
        }

        $this->redirect('institute/basicdata/index?cid=');
    }
}
