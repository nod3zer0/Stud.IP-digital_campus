<?php
/**
 * PublicCoursesController - Shows an overview of all courses with public
 * access
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.0
 */

class PublicCoursesController extends AuthenticatedController
{
    protected $allow_nobody = true;

    /**
     * Initializes the controller.
     *
     * @param string $action Action to execute
     * @param array  $args   Passed parameters
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!Config::get()->ENABLE_FREE_ACCESS) {
            throw new AccessDeniedException(_('Öffentliche Veranstaltungen sind nicht aktiviert.'));
        }

        Navigation::activateItem('/browse');

        PageLayout::setTitle(_('Öffentliche Veranstaltungen'));
        PageLayout::setHelpKeyword('Basis.SymboleFreieVeranstaltungen');

        // we are definitely not in an lexture or institute
        closeObject();
    }

    /**
     * Displays a list of all public courses
     */
    public function index_action()
    {
        $query = "SELECT Seminar_id, seminare.Name AS name, seminare.status, seminare.Schreibzugriff,
                         Institute.Name AS Institut, Institut_id AS id
                  FROM seminare
                  LEFT JOIN Institute USING (Institut_id)
                  WHERE Lesezugriff = '0' AND seminare.visible = '1'
                  ORDER BY :order";
        $statement = DBManager::get()->prepare($query);
        $statement->bindParam(':order', Request::option('sortby', 'Name'), StudipPDO::PARAM_COLUMN);
        $statement->execute();

        $seminars = $statement->fetchGrouped(PDO::FETCH_ASSOC);

        $seminars = $this->get_seminar_navigations($seminars);
        $this->seminars = $seminars;
    }

    /**
     * Adds all navigation entries for each passed seminar.
     *
     * @param array $seminars List of seminars
     * @return array Extended list of seminars
     */
    protected function get_seminar_navigations($seminars)
    {
        if (empty($seminars)) {
            return [];
        }

        foreach ($seminars as $id => $seminar) {
            $seminar['navigations'] = [];
            $seminar['tools'] = new SimpleCollection(ToolActivation::findByrange_id($id));
            foreach (MyRealmModel::getDefaultModules() as $plugin_id => $plugin) {

                // Go to next module if current module is not available and not voting-module
                if ($plugin !== 'vote' && !$seminar['tools']->findOneBy('plugin_id', $plugin_id)) {
                    $seminar['navigations'][get_class($plugin)] = null;
                    continue;
                }

                if (!Config::get()->VOTE_ENABLE && $plugin_id === 'vote') {
                    continue;
                }

                if ($plugin === 'vote') {
                    $seminar['navigations'][$plugin] = false;
                } else if ($tool = $seminar['tools']->findOneBy('plugin_id', $plugin_id)) {
                    if ($tool->getVisibilityPermission() === 'nobody') {
                        $seminar['navigations'][get_class($plugin)] = false;
                    } else {
                        $seminar['navigations'][get_class($plugin)] = null;
                    }
                }
            }
            $seminars[$id] = $seminar;
        }

        $seminar_ids = array_keys($seminars);

        // Documents
        $query = "SELECT range_id, COUNT(*) AS count
                  FROM folders
                  INNER JOIN file_refs ON folder_id = folders.id
                  WHERE range_id IN (?) AND range_type = 'course'
                  AND folder_type IN ('RootFolder', 'StandardFolder')
                  GROUP BY range_id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$seminar_ids]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            if (isset($seminars[$row['range_id']]['navigations']['CoreDocuments'])) {
                $nav = new Navigation('files', 'dispatch.php/course/files/index');
                $nav->setImage(Icon::create('files', Icon::ROLE_CLICKABLE, ["title" => sprintf(_('%s Dokumente'), $row['count'])]));
                $seminars[$row['range_id']]['navigations']['CoreDocuments'] = $nav;
            }
        }

        // News
        $query = "SELECT range_id, COUNT(*) AS count
                  FROM news_range
                  LEFT JOIN news USING (news_id)
                  WHERE range_id IN (?)
                  GROUP BY range_id
                  HAVING count > 0";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$seminar_ids]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            if (isset($seminars[$row['range_id']]['navigations']['CoreOverview'])) {
                $nav = new Navigation('news', '');
                $nav->setImage(Icon::create('news', Icon::ROLE_CLICKABLE, ["title" => sprintf(_('%s Ankündigungen'),$row['count'])]));
                $seminars[$row['range_id']]['navigations']['CoreOverview'] = $nav;
            }
        }

        // Information
        $query = "SELECT range_id, COUNT(*) AS count
                  FROM scm
                  WHERE range_id IN (?)
                  GROUP BY range_id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$seminar_ids]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            if (isset($seminars[$row['range_id']]['navigations']['CoreScm'])) {
                $nav = new Navigation('scm', 'dispatch.php/course/scm');
                $nav->setImage(Icon::create('infopage', Icon::ROLE_CLICKABLE, ["title" => sprintf(_('%s Einträge'), $row['count'])]));
                $seminars[$row['range_id']]['navigations']['CoreScm'] = $nav;
            }
        }

        // Appointments
        $query = "SELECT range_id, COUNT(*) AS count
                  FROM termine
                  WHERE range_id IN (?)
                  GROUP BY range_id";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$seminar_ids]);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            if (isset($seminars[$row['range_id']]['navigations']['CoreSchedule'])) {
                $nav = new Navigation('schedule', 'dispatch.php/course/dates');
                $nav->setImage(Icon::create('schedule', Icon::ROLE_CLICKABLE, ["title" => sprintf(_('%s Termine'), $row['count'])]));
                $seminars[$row['range_id']]['navigations']['CoreSchedule'] = $nav;
            }
        }

        // Wiki
        if (Config::get()->WIKI_ENABLE) {
            $query = "SELECT `range_id`, COUNT(`wiki_versions`.`version_id`) + 1 AS count
                      FROM `wiki_pages`
                      LEFT JOIN `wiki_versions` USING (`page_id`)
                      WHERE `range_id` IN (?)
                      GROUP BY `range_id`";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$seminar_ids]);
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                if (isset($seminars[$row['range_id']]['navigations']['CoreWiki'])) {
                    $nav = new Navigation(
                        'wiki',
                        URLHelper::getURL('dispatch.php/course/wiki/page', ['cid' => $row['range_id']])
                    );
                    $nav->setImage(Icon::create('wiki', Icon::ROLE_CLICKABLE, ["title" => sprintf(_('%s WikiSeiten'), $row['count'])]));
                    $seminars[$row['range_id']]['navigations']['CoreWiki'] = $nav;
                }
            }
        }

        // Votes
        if (Config::get()->VOTE_ENABLE) {
            $query = "SELECT questionnaire_assignments.range_id, COUNT(DISTINCT questionnaires.questionnaire_id) AS count
                      FROM questionnaires
                          INNER JOIN questionnaire_assignments ON (questionnaire_assignments.questionnaire_id = questionnaires.questionnaire_id)
                      WHERE questionnaires.visible = '1'
                          AND questionnaire_assignments.range_id IN (?)
                      GROUP BY questionnaire_assignments.range_id ";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$seminar_ids]);
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                if (isset($seminars[$row['range_id']]['navigations']['vote'])) {
                    $nav = new Navigation('vote', '#vote');
                    $nav->setImage(Icon::create('vote', Icon::ROLE_CLICKABLE, ["title" => sprintf(_('%s Umfrage(n)'), $row['count'])]));
                    $seminars[$row['range_id']]['navigations']['vote'] = $nav;
                }
            }
        }

        foreach ($seminars as $id => $seminar) {
            foreach ($seminar['tools'] as $tool) {
                $module = $tool->getStudipModule();
                if (!$module || in_array(get_class($module), ['CoreAdmin', 'CoreStudygroupAdmin', 'CoreWiki', 'CoreSchedule', 'CoreScm', 'CoreOverview', 'CoreDocuments'])) {
                    continue;
                }

                if ($tool->getVisibilityPermission() === 'nobody') {
                    $seminar['navigations'][get_class($module)] = $module->getIconNavigation($id, time(), 'nobody');
                } else {
                    $seminar['navigations'][get_class($module)] = null;
                }
            }
            $seminars[$id] = $seminar;
        }
        return $seminars;
    }
}
