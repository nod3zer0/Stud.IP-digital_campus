<?php
# Lifter010: TODO

/*
 * Copyright (C) 2014 - Rasmus Fuhse <fuhse@data-quest.de>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */

class Course_OverviewController extends AuthenticatedController
{
    protected $allow_nobody = true;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        checkObject();
        $this->course = Course::findCurrent();
        if (!$this->course) {
            throw new CheckObjectException(_('Sie haben kein Objekt gewählt.'));
        }

        $this->course_id = $this->course->id;

        PageLayout::setHelpKeyword('Basis.InVeranstaltungKurzinfo');
        PageLayout::setTitle(Context::getHeaderLine() . ' - ' . _('Kurzinfo'));
        Navigation::activateItem('/course/main/info');

        $this->sem             = Seminar::getInstance($this->course_id);
        $sem_class             = $this->sem->getSemClass();
        $this->studygroup_mode = $sem_class['studygroup_mode'];
    }

    /**
     * This method is called to show the form to upload a new avatar for a
     * course.
     * @return void
     */
    public function index_action()
    {
        if (Config::get()->NEWS_RSS_EXPORT_ENABLE && $this->course_id) {
            $rss_id = StudipNews::GetRssIdFromRangeId($this->course_id);
            if ($rss_id) {
                PageLayout::addHeadElement('link', ['rel'   => 'alternate',
                                                    'type'  => 'application/rss+xml',
                                                    'title' => 'RSS',
                                                    'href'  => 'rss.php?id=' . $rss_id]);
            }
        }

        // Fetch news
        $response   = $this->relayWithRedirect('news/display/' . $this->course_id);
        $this->news = $response->body;

        // Fetch  votes
        if (Config::get()->VOTE_ENABLE) {
            $response             = $this->relay('evaluation/display/' . $this->course_id);
            $this->evaluations    = $response->body;
            $response             = $this->relay('questionnaire/widget/' . $this->course_id);
            $this->questionnaires = $response->body;
        }


        if (!$this->studygroup_mode) {
            $this->avatar = CourseAvatar::getAvatar($this->course_id);
            // Fetch dates
            $response          = $this->relay("calendar/contentbox/display/{$this->course_id}/1210000");
            $this->dates       = $response->body;
            $this->next_date   = $this->sem->getNextDate();
            $this->first_date  = $this->sem->getFirstDate();
            $show_link         = $GLOBALS["perm"]->have_studip_perm('autor', $this->course_id) && $this->course->isToolActive('schedule');
            $this->times_rooms = $this->sem->getDatesTemplate('dates/seminar_html', ['link_to_dates' => $show_link, 'show_room' => true]);

            // Fettch teachers
            $dozenten      = $this->sem->getMembers('dozent');
            $this->num_dozenten  = count($dozenten);
            $show_dozenten = [];
            foreach ($dozenten as $dozent) {
                $show_dozenten[] = '<a href="' . URLHelper::getLink('dispatch.php/profile', ['username' => $dozent['username']]) . '">'
                    . htmlready($this->num_dozenten > 10 ? get_fullname($dozent['user_id'], 'no_title_short') : $dozent['fullname'])
                    . '</a>';
            }
            $this->show_dozenten = $show_dozenten;

            // Check lock rules
            if (!$GLOBALS['perm']->have_studip_perm('dozent', $this->course_id)) {
                $rule = AuxLockRule::findOneByCourse($this->course);
                if ($rule && count($rule->attributes) > 0) {
                    $count = DataField::countBySql("LEFT JOIN datafields_entries USING (datafield_id) WHERE object_type = ? AND sec_range_id = ? AND range_id = ?",
                        ['usersemdata', $this->course_id, $GLOBALS['user']->id]
                    );

                    if ($count === 0) {
                        PageLayout::postInfo(
                            _("Sie haben noch nicht die für diese Veranstaltung benötigten Zusatzinformationen eingetragen."),
                            [
                                sprintf(
                                    _('Um das nachzuholen, gehen Sie unter "Teilnehmende" auf "Zusatzangaben" oder %1$s direkt zu den Zusatzangaben. %2$s'),
                                    '<a href="' . URLHelper::getLink('dispatch.php/course/members/additional_input') . '">',
                                    '</a>'
                                )
                            ]
                        );
                    }
                }
            }
        } else {
            $this->all_mods = $this->sem->getMembers('dozent') + $this->sem->getMembers('tutor');
            $this->avatar   = StudygroupAvatar::getAvatar($this->course_id);
        }

        $this->plugins = PluginEngine::getPlugins('StandardPlugin', $this->course_id);

        $sidebar = Sidebar::get();

        if (!$this->course->admission_binding
            && in_array($GLOBALS['perm']->get_studip_perm($this->course->id), ['user','autor'])
            && !$this->course->getSemClass()->isGroup())
        {
            $actions = new ActionsWidget();
            $actions->addLink(
                _('Austragen aus der Veranstaltung'),
                $this->url_for("my_courses/decline/{$this->course->id}", ['cmd' => 'suppose_to_kill']),
                Icon::create('door-leave')
            )->setDisabled(!empty($_SESSION["seminar_change_view_{$this->course_id}"]));
            Sidebar::get()->addWidget($actions);
        }

        $share = new ShareWidget();
        if ($this->studygroup_mode) {
            $share->addCopyableLink(
                _('Link zu dieser Studiengruppe kopieren'),
                $this->url_for('course/studygroup/details/' . $this->course->id, [
                    'cid'   => null,
                    'again' => 'yes',
                ]),
                Icon::create('clipboard')
            );
        } else {
            $share->addCopyableLink(
                _('Link zu dieser Veranstaltung kopieren'),
                $this->url_for('course/details', [
                    'sem_id' => $this->course->id,
                    'cid'    => null,
                    'again'  => 'yes',
                ]),
                Icon::create('clipboard')
            );
        }
        $sidebar->addWidget($share);
    }
}
