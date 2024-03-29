<?php

/**
 * ContensDashboardNavigation.php - navigation for contents dashboard
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Ron Lucke <lucke@elan-ev.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 *
 * @category    Stud.IP
 */
class ContentsNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        parent::__construct(_('Arbeitsplatz'));

        $this->setImage(Icon::create('content', 'navigation', ['title' => _('Mein Arbeitsplatz')]));
    }

    /**
     * Initialize the subnavigation of this item. This method
     * is called once before the first item is added or removed.
     */
    public function initSubNavigation()
    {
        parent::initSubNavigation();
        global $perm;

        $overview = new Navigation(_('Übersicht'));
        $overview->addSubNavigation(
            'index',
            new Navigation(_('Übersicht'), 'dispatch.php/contents/overview')
        );

        $this->addSubNavigation('overview', $overview);

        if (PluginManager::getInstance()->getPlugin('CoursewareModule')) {
            $courseware = new Navigation(_('Courseware'));
            $courseware->setDescription(_('Erstellen und Sammeln von Lernmaterialien'));
            $courseware->setImage(Icon::create('courseware'));

        $courseware = new Navigation(_('Courseware'));
        $courseware->setDescription(_('Erstellen und Sammeln von Lernmaterialien'));
        $courseware->setImage(Icon::create('courseware'));

        $courseware->addSubNavigation(
            'shelf',
            new Navigation(_('Lernmaterialien'), 'dispatch.php/contents/courseware/index')
        );
        $courseware->addSubNavigation(
            'courseware',
            new Navigation(_('Inhalt'), 'dispatch.php/contents/courseware/courseware')
        );
        $courseware->addSubNavigation(
            'releases',
            new Navigation(_('Freigaben'), 'dispatch.php/contents/courseware/releases')
        );
        $courseware->addSubNavigation(
            'bookmarks',
            new Navigation(_('Lesezeichen'), 'dispatch.php/contents/courseware/bookmarks')
        );
        $courseware->addSubNavigation(
            'courses_overview',
            new Navigation(_('Meine Veranstaltungen'), 'dispatch.php/contents/courseware/courses_overview')
        );

        $this->addSubNavigation('courseware', $courseware);

            $this->addSubNavigation('courseware', $courseware);
        }

        $files = new Navigation(_('Dateien'));
        $files->setDescription(_('Überblick über alle Dokumente'));
        $files->setImage(Icon::create('files'));

        $files->addSubNavigation(
            'overview',
            new Navigation(_('Übersicht'), 'dispatch.php/files/overview')
        );
        $files->addSubNavigation(
            'my_files',
            new Navigation(_('Persönliche Dateien'), 'dispatch.php/files/index')
        );
        $files->addSubNavigation(
            'search',
            new Navigation(_('Suche'), 'dispatch.php/files_dashboard/search')
        );

        $this->addSubNavigation('files', $files);

        // news
        $news = new Navigation(_('Ankündigungen'), 'dispatch.php/news/admin_news');
        $news->setImage(Icon::create('news'));
        $news->setDescription(_('Verwaltung von Ankündigungen in Ihren Bereichen'));
        $this->addSubNavigation('news', $news);

        // votes and tests, evaluations
        if (Config::get()->VOTE_ENABLE) {
            $questionnaire = new Navigation(_('Fragebögen'), 'dispatch.php/questionnaire/overview');
            $questionnaire->setImage(Icon::create('evaluation'));
            $questionnaire->setDescription(_('Zentrale Sammlung Ihrer Fragebögen'));
            $this->addSubNavigation('questionnaire', $questionnaire);

            $sub_nav = new Navigation(
                _('Übersicht'),
                'dispatch.php/questionnaire/overview'
            );
            $questionnaire->addSubNavigation('overview', $sub_nav);

            if ($GLOBALS['perm']->have_perm('admin')) {
                $sub_nav = new Navigation(
                    _('Fragebögen zuordnen'),
                    'dispatch.php/questionnaire/assign'
                );
                $questionnaire->addSubNavigation('assign', $sub_nav);
            }
        }

        if (Config::get()->EVAL_ENABLE) {
            $eval = new Navigation(_('Evaluationen'), 'admin_evaluation.php', ['rangeID' => $GLOBALS['user']->username]);
            $eval->setImage(Icon::create('test'));
            $eval->setDescription(_('Erstellen Sie komplexe Befragungen'));
            $this->addSubNavigation('evaluation', $eval);
        }

        // elearning
        if (Config::get()->ELEARNING_INTERFACE_ENABLE) {
            $elearning = new Navigation(_('Lernmodule'), 'dispatch.php/elearning/my_accounts');
            $elearning->setImage(Icon::create('learnmodule'));
            $elearning->setDescription(_('Zugang zu externen Lernmaterialien'));
            $this->addSubNavigation('my_elearning', $elearning);
        }

        if (!$GLOBALS['perm']->have_perm('root') && $GLOBALS['user']->getAuthenticatedUser()->hasRole('Hilfe-Administrator(in)')) {
            $help = new Navigation(_('Hilfe'), 'dispatch.php/help_content/admin_overview');
            $help->setImage(Icon::create('question-circle'));
            $help->setDescription(_('Verwaltung der Hilfe-Inhalte in diesem Stud.IP'));
            $this->addSubNavigation('help_admin', $help);
            if (Config::get()->TOURS_ENABLE) {
                $help->addSubNavigation('tour', new Navigation(_('Touren'), 'dispatch.php/tour/admin_overview'));
            }
            $help->addSubNavigation('help_content', new Navigation(_('Hilfe-Texte'), 'dispatch.php/help_content/admin_overview'));
        }

    }
}
