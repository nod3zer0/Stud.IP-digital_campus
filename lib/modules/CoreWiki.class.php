<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

class CoreWiki extends CorePlugin implements StudipModule
{
    /**
     * {@inheritdoc}
     */
    public function getIconNavigation($range_id, $last_visit, $user_id)
    {
        if (!Config::get()->WIKI_ENABLE) {
            return null;
        }
        $perm = $GLOBALS['perm']->get_perm($user_id);
        if (in_array($perm, ['admin', 'root'])) {
            $perm = 'dozent';
        }

        $statement = DBManager::get()->prepare("
            SELECT `wiki_pages`.`page_id`
            FROM `wiki_pages`
                LEFT JOIN `statusgruppe_user` ON (`statusgruppe_user`.`statusgruppe_id` = `wiki_pages`.`read_permission`)
            WHERE `wiki_pages`.`range_id` = :range_id
                AND (
                    `wiki_pages`.`read_permission` = 'all'
                    OR `statusgruppe_user`.`user_id` = :user_id
                    OR `wiki_pages`.`read_permission` = :perm
                    OR (`wiki_pages`.`read_permission` = 'tutor' AND :perm = 'dozent')
                )
        ");

        $statement->execute([
            'range_id' => $range_id,
            'user_id' => $user_id,
            'perm' => $perm
        ]);
        $wiki_page_ids = $statement->fetchAll(PDO::FETCH_COLUMN);
        if (count($wiki_page_ids) === 0) {
            return null;
        }

        $visit_date = object_get_visit($range_id, $this->getPluginId(), 'visitdate') ?? $last_visit;

        $statement = DBManager::get()->prepare("
            SELECT COUNT(*) AS `neue`
            FROM `wiki_pages`
            WHERE `wiki_pages`.`page_id` IN (:page_ids)
                AND `wiki_pages`.`chdate` > :threshold
                AND `wiki_pages`.`user_id` != :user_id
        ");
        $statement->execute([
            'page_ids' => $wiki_page_ids,
            'threshold' => $visit_date,
            'user_id' => $user_id,
        ]);
        $new_pages = $statement->fetch(PDO::FETCH_COLUMN, 0);

        $nav = new Navigation(_('Wiki'));
        if ($new_pages > 0) {
            $nav->setURL('dispatch.php/course/wiki/newpages');
            $nav->setImage(Icon::create('wiki', Icon::ROLE_ATTENTION, [
                'title' => sprintf(
                    ngettext(
                        '%d Wiki-Seite',
                        '%d Wiki-Seiten',
                        count($wiki_page_ids)
                    ),
                    count($wiki_page_ids)
                )
                . ', '
                . sprintf(
                    ngettext(
                        '%d Änderung',
                        '%d Änderungen',
                        $new_pages
                     ),
                        $new_pages
                 )
            ]));
            $nav->setBadgeNumber($new_pages);
        } else {
            $nav->setURL('dispatch.php/course/wiki/page');
            $nav->setImage(Icon::create('wiki', Icon::ROLE_CLICKABLE, [
                'title' => sprintf(
                    ngettext(
                        '%d Wiki-Seite',
                        '%d Wiki-Seiten',
                        count($wiki_page_ids)
                    ),
                    count($wiki_page_ids)
                )
            ]));
        }
        return $nav;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabNavigation($range_id)
    {
        if (!Config::get()->WIKI_ENABLE) {
            return null;
        }

        $navigation = new Navigation(_('Wiki'));
        $navigation->setImage(Icon::create('wiki', Icon::ROLE_INFO_ALT));
        $navigation->setActiveImage(Icon::create('wiki', Icon::ROLE_INFO));

        $navigation->addSubNavigation('start', new Navigation(_('Wiki-Startseite'), 'dispatch.php/course/wiki/page'));
        if (WikiPage::countBySQL('`range_id` = ?', [$range_id]) > 0) {
            $navigation->addSubNavigation('listnew', new Navigation(_('Neue Seiten'), 'dispatch.php/course/wiki/newpages'));
            $navigation->addSubNavigation('allpages', new Navigation(_('Alle Seiten'), 'dispatch.php/course/wiki/allpages'));
        }
        return ['wiki' => $navigation];
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return [
            'summary' => _('Gemeinsames Erstellen und Bearbeiten von Texten'),
            'description' => _('Im Wiki können '.
                'verschiedene Autor/-innen gemeinsam Texte, Konzepte und andere '.
                'schriftliche Arbeiten erstellen und gestalten, dies '.
                'allerdings nicht gleichzeitig. Texte können individuell '.
                'bearbeitet und die Änderungen gespeichert werden. Das '.
                'Besondere im Wiki ist, dass Studierende und Lehrende '.
                'annähernd die gleichen Rechte (schreiben, lesen, ändern, '.
                'löschen) haben, was sich nicht einschränken lässt. Das '.
                'System erstellt eine Versionshistorie, mit der Änderungen '.
                'nachvollziehbar werden. Einzelne Versionen können zudem '.
                'auch gelöscht werden (nur Lehrende). Ein Export als '.
                'pdf-Datei ist integriert.'),

            'displayname' => _('Wiki-Web'),
            'keywords' => _('Individuelle Bearbeitung von Texten;
                            Versionshistorie;
                            Druckansicht und PDF-Export;
                            Löschfunktion für die aktuellste Seiten-Version;
                            Keine gleichzeitige Bearbeitung desselben Textes möglich, nur nacheinander'),
            'descriptionshort' => _('Gemeinsames asynchrones Erstellen und Bearbeiten von Texten'),
            'descriptionlong' => _('Im Wiki können verschiedene Autor/-innen gemeinsam Texte, '.
                                    'Konzepte und andere schriftliche Arbeiten erstellen und gestalten. Dies '.
                                    'allerdings nicht gleichzeitig. Texte können individuell bearbeitet und '.
                                    'gespeichert werden. Das Besondere im Wiki ist, dass Studierende und Lehrende '.
                                    'annähernd die gleichen Rechte (schreiben, lesen, ändern, löschen) haben, was '.
                                    'gegenseitiges Vertrauen voraussetzt. Das System erstellt eine Versionshistorie, '.
                                    'mit der Änderungen nachvollziehbar werden. Einzelne Versionen können zudem auch '.
                                    'gelöscht werden (nur Lehrende). Eine Druckansicht und eine Exportmöglichkeit als '.
                                    'PDF-Datei ist integriert.'),
            'category' => _('Kommunikation und Zusammenarbeit'),
            'icon' => Icon::create('wiki', Icon::ROLE_INFO),
            'icon_clickable' => Icon::create('wiki', Icon::ROLE_CLICKABLE),
            'screenshots' => [
                'path' => 'assets/images/plus/screenshots/Wiki-Web',
                'pictures' => [
                    0 => [ 'source' => 'Gemeinsam_erstellte_Texte.jpg', 'title' => 'Gemeinsam erstellte Texte']
                ]
            ]
        ];
    }

    public function getInfoTemplate($course_id)
    {
        return null;
    }


    /**
     * Generates a page hierarchy for table of contents/breadcrumbs.
     * @return TOCItem
     */
    public static function getTOC($startPage, $active_title = null): TOCItem
    {
        $root = new TOCItem($startPage->isNew() || $startPage->name === 'WikiWikiWeb'
            ? _('Wiki-Startseite')
            : $startPage->name
        );
        $root->setURL(URLHelper::getURL('dispatch.php/course/wiki/page/'.$startPage->id));
        if ($startPage->name == 'WikiWikiWeb' || $startPage->id == CourseConfig::get($startPage->range_id)->WIKI_STARTPAGE_ID) {
            $root->setIcon(Icon::create('wiki'));
        }
        $root->setActive($root->getTitle() === $active_title);

        foreach ($startPage->children as $child) {
            $item = self::getTOC($child, $active_title);
            $item->setActive($item->getTitle() === $active_title);
            $root->addChild($item);
        }

        return $root;
    }

}
