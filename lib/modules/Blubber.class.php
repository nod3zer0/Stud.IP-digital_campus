<?php
/*
 *  Copyright (c) 2012-2019  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

/**
 * Class Blubber - the Blubber-plugin
 * This is only used to manage blubber within a course.
 */
class Blubber extends CorePlugin implements StudipModule
{
    /**
     * Returns a navigation for the tab displayed in the course.
     * @param string $course_id of the course
     * @return \Navigation
     */
    public function getTabNavigation($course_id)
    {
        $tab = new Navigation(
            _('Blubber'),
            'dispatch.php/course/messenger/course'
        );
        $tab->setImage(Icon::create('blubber', Icon::ROLE_INFO_ALT));
        return ['blubber' => $tab];
    }

    /**
     * Returns a navigation-object with the grey/red icon for displaying in the
     * my_courses.php page.
     * @param string  $course_id
     * @param int $last_visit
     * @param string|null  $user_id
     * @return \Navigation
     */
    public function getIconNavigation($course_id, $last_visit, $user_id = null)
    {
        $user_id || $user_id = $GLOBALS['user']->id;
        $icon = new Navigation(
            _('Blubber'),
            'dispatch.php/course/messenger/course'
        );
        $icon->setImage(Icon::create('blubber', Icon::ROLE_CLICKABLE, ['title' => _('Blubber-Messenger')]));

        $condition = "INNER JOIN blubber_threads USING (thread_id)
                      WHERE blubber_threads.context_type = 'course'
                        AND blubber_threads.context_id = :course_id
                        AND blubber_comments.mkdate >= :last_visit
                        AND blubber_comments.user_id != :me
                        AND blubber_threads.visible_in_stream = 1
                        ";
        $comments = BlubberComment::findBySQL($condition, [
            'course_id'  => $course_id,
            'last_visit' => $last_visit,
            'me'         => $user_id,
        ]);
        foreach ($comments as $comment) {
            if (
                $comment->thread->isVisibleInStream()
                && $comment->thread->isReadable()
                && $comment->thread->getLatestActivity() > $comment->thread->getLastVisit()
            ) {
                $icon->setImage(Icon::create('blubber', Icon::ROLE_NEW, ['title' => _('Es gibt neue Blubber')]));
                $icon->setTitle(_('Es gibt neue Blubber'));
                $icon->setBadgeNumber(count($comments));
                $icon->setURL('dispatch.php/course/messenger/course', ['thread' => 'new']);
                break;
            }
        }

        $condition = "context_type = 'course'
                        AND context_id = :course_id
                        AND mkdate >= :last_visit
                        AND user_id != :me
                        AND visible_in_stream = 1
                        AND (
                            blubber_threads.display_class IS NOT NULL
                            OR blubber_threads.`content` IS NOT NULL
                        )";
        $threads = BlubberThread::findBySQL($condition, [
            'course_id'  => $course_id,
            'last_visit' => $last_visit,
            'me'         => $GLOBALS['user']->id,
        ]);
        foreach ($threads as $thread) {
            if (
                $thread->isVisibleInStream()
                && $thread->isReadable()
                && $thread->mkdate > $thread->getLastVisit()
            ) {
                $icon->setImage(Icon::create('blubber', Icon::ROLE_ATTENTION, ['title' => _('Es gibt neue Blubber')]));
                $icon->setTitle(_('Es gibt neue Blubber'));
                break;
            }
        }
        return $icon;
    }

    /**
     * Returns no template, because this plugin doesn't want to insert an
     * info-template in the course-overview.
     * @param string $course_id
     * @return null
     */
    public function getInfoTemplate($course_id)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return [
            'summary' => _('Schneller und einfacher Austausch von Informationen in Gesprächsform'),
            'description' => _('Blubber ist eine Kommunikationsform mit Ähnlichkeiten zu einem Forum, in dem aber in Echtzeit miteinander kommuniziert werden kann und das durch den etwas informelleren Charakter eher einem Chat anmutet. Anders als im Forum ist es nicht notwendig, die Seiten neu zu laden, um die neuesten Einträge (z. B. Antworten auf eigene Postings) sehen zu können: Die Seite aktualisiert sich selbst bei neuen Einträgen. Dateien (z.B. Fotos, Audiodateien, Links) können per Drag and Drop in das Feld gezogen und somit verlinkt werden. Auch Textformatierungen und das Verwenden von Smileys sind möglich.'),
            'descriptionlong' => _('Kommunikationsform mit Ähnlichkeiten zu einem Forum. Im Gegensatz zum Forum kann mit Blubber jedoch in Echtzeit miteinander kommuniziert werden. Das Tool ähnelt durch den etwas informelleren Charakter einem Messenger. Anders als im Forum ist es nicht notwendig, die Seiten neu zu laden, um die neuesten Einträge (z. B. Antworten auf eigene Postings) sehen zu können. Dateien (z. B. Fotos, Audiodateien, Links) können per drag and drop in das Feld gezogen und somit verlinkt werden. Auch Textformatierungen und das Verwenden von Smileys sind möglich.'),
            'category' => _('Kommunikation und Zusammenarbeit'),
            'keywords' => _('Einfach Text schreiben und mit <Enter> abschicken; Direktes Kontaktieren anderer Stud.IP-NutzerInnen (@Vorname Nachname); Setzen von und Suche nach Stichworten über Hashtags (#Stichwort); Einbinden von Dateien per drag and drop'),
            'icon' => Icon::create('blubber', Icon::ROLE_INFO),
            'screenshots' => [
                'path' => 'assets/images/plus/screenshots/Blubber',
                'pictures' => [
                    ['source' => 'blubberscreenshot.png', 'title' => 'Blubbern']
                ]
            ]
        ];
    }
}
