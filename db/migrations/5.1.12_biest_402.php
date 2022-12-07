<?php
class Biest402 extends Migration
{
    public function description()
    {
        return "Update help tours in Stud.IP.";
    }

    public function isTourChanged($tour_id) {
        $statement = DBManager::get()->prepare("
            SELECT COUNT(*)
            FROM `help_tour_steps` WHERE tour_id = ? AND `chdate` > `mkdate`
        ");
        $statement->execute([$tour_id]);
        $changed = $statement->fetch(PDO::FETCH_COLUMN, 0);
        if (!$changed) {
            $statement = DBManager::get()->prepare("
                SELECT COUNT(*)
                FROM `help_tour_settings` WHERE tour_id = ? AND `chdate` > `mkdate`
            ");
            $statement->execute([$tour_id]);
            $changed = $statement->fetch(PDO::FETCH_COLUMN, 0);
        }
        return $changed > 0;
    }

    public function removeTour($tour_id, $remove_user_data = false) {
        $statement = DBManager::get()->prepare("
            DELETE FROM `help_tours` WHERE tour_id = ?;
        ");
        $statement->execute([$tour_id]);
        $statement = DBManager::get()->prepare("
            DELETE FROM `help_tour_steps` WHERE tour_id = ?;
        ");
        $statement->execute([$tour_id]);
        $statement = DBManager::get()->prepare("
            DELETE FROM `help_tour_audiences` WHERE tour_id = ?;
        ");
        $statement->execute([$tour_id]);
        $statement = DBManager::get()->prepare("
            DELETE FROM `help_tour_settings` WHERE tour_id = ?;
        ");
        $statement->execute([$tour_id]);
        if ($remove_user_data) {
            $statement = DBManager::get()->prepare("
                DELETE FROM `help_tour_user` WHERE tour_id = ?;
            ");
            $statement->execute([$tour_id]);
        }
    }

    public function updateTour($tour_id, $tour, $settings, $steps) {
        $tour_changed = $this->isTourChanged($tour_id);
        if ($tour_changed) {
            $old_tour_id = md5(uniqid('tours', 1));
            $query = "UPDATE `help_tours` SET `tour_id` = :old_tour_id, `name` = CONCAT(`name`, ' (outdated)') WHERE `tour_id` = :tour_id";
            DBManager::get()->execute($query, [
                'old_tour_id' => $old_tour_id,
                'tour_id'     => $tour_id,
            ]);

            $query = "UPDATE `help_tour_steps` SET `tour_id` = :old_tour_id WHERE `tour_id` = :tour_id";
            DBManager::get()->execute($query, [
                'old_tour_id' => $old_tour_id,
                'tour_id'     => $tour_id,
            ]);

            $query = "UPDATE `help_tour_audiences` SET `tour_id` = :old_tour_id WHERE `tour_id` = :tour_id";
            DBManager::get()->execute($query, [
                'old_tour_id' => $old_tour_id,
                'tour_id'     => $tour_id,
            ]);

            $query = "UPDATE `help_tour_settings` SET `tour_id` = :old_tour_id, `active` = 0 WHERE `tour_id` = :tour_id";
            DBManager::get()->execute($query, [
                'old_tour_id' => $old_tour_id,
                'tour_id'     => $tour_id,
            ]);

            $query = "UPDATE `help_tour_user` SET `tour_id` = :old_tour_id WHERE `tour_id` = :tour_id";
            DBManager::get()->execute($query, [
                'old_tour_id' => $old_tour_id,
                'tour_id'     => $tour_id,
            ]);
        } else {
            $this->removeTour($tour_id, false);
        };

        $query = "INSERT INTO `help_tours` (`global_tour_id`, `tour_id`, `name`, `description`, `type`, `roles`, `version`, `language`, `studip_version`, `installation_id`, `author_email`, `mkdate`, `chdate`) VALUES " . $tour;
        $statement = DBManager::get()->prepare($query);
        $statement->execute(['tour_id' => $tour_id]);

        $query = "INSERT INTO `help_tour_settings` (`tour_id`, `active`, `access`, `mkdate`, `chdate`) VALUES " . $settings;
        $statement = DBManager::get()->prepare($query);
        $statement->execute(['tour_id' => $tour_id]);

        $query = "INSERT INTO `help_tour_steps` (`tour_id`, `step`, `title`, `tip`, `orientation`, `interactive`, `css_selector`, `route`, `action_prev`, `action_next`, `author_email`, `mkdate`, `chdate`) VALUES " . $steps;
        $statement = DBManager::get()->prepare($query);
        $statement->execute(['tour_id' => $tour_id]);
    }

    public function up()
    {
        $this->removeTour('0b542c6c891af499763356f2c7218f7f', true);
        $this->removeTour('977f41c5c5239c4e86f04c3df27fae38', true);
        $this->removeTour('e41611616675b218845fe9f55bc11cf6', true);
        $this->removeTour('25e7421f286fc5bdf9e41beadb484ffa', true);

        // add new tours "Stud.IP 5", "Mein Arbeitsplatz", "OER Campus"
        DBManager::get()->exec("
            INSERT IGNORE INTO `help_tours` (`global_tour_id`, `tour_id`, `name`, `description`, `type`, `roles`, `version`, `language`, `studip_version`, `installation_id`, `author_email`, `mkdate`, `chdate`) VALUES
            ('0316ff46356293ef49d8e7db09485be0', 'a848744bde4dac47ec2e8b383155381d', 'Stud.IP 5', 'Einführung in Stud.IP 5', 'tour', 'tutor,dozent,admin,root', 1, 'de', '5.0', 'demo-installation', 'dozent@studip.de', 1631612143, 1631612143),
            ('0316ff46356293ef49d8e7db09485be0', 'dac47ec2e8a848744bde4b3881d31553', 'Stud.IP 5', 'Einführung in Stud.IP 5', 'tour', 'autor', 1, 'de', '5.0', 'demo-installation', 'dozent@studip.de', 1631612143, 1631612143),
            ('8e7a9f6b86255bc9034b71d8318419e6', 'd9a066071e2be43b2b51c37a9d692026', 'OER Campus', 'Einführung in den OER Campus', 'tour', 'autor,tutor,dozent,admin,root', 1, 'de', '5.0', 'demo-installation', 'root@localhost', 1631614324, 1631614324),
            ('36821ce84e48f0bd68482f9f43099460', '55f3a548348dcbfdca67678588887ffd', 'Mein Arbeitsplatz', 'Einführung Mein Arbeitsplatz', 'tour', 'autor,tutor,dozent,admin,root', 1, 'de', '5.0', 'demo-installation', 'root@localhost', 1631614324, 1631614324)
        ");

        DBManager::get()->exec("
            INSERT IGNORE INTO `help_tour_settings` (`tour_id`, `active`, `access`, `mkdate`, `chdate`) VALUES
            ('a848744bde4dac47ec2e8b383155381d', 1, 'autostart_once', 1631612143, 1631612143),
            ('dac47ec2e8a848744bde4b3881d31553', 1, 'autostart_once', 1631612143, 1631612143),
            ('d9a066071e2be43b2b51c37a9d692026', 1, 'autostart_once', 1631612143, 1631612143),
            ('55f3a548348dcbfdca67678588887ffd', 1, 'autostart_once', 1631612143, 1631612143)
        ");

        DBManager::get()->exec("
            INSERT IGNORE INTO `help_tour_steps` (`tour_id`, `step`, `title`, `tip`, `orientation`, `interactive`, `css_selector`, `route`, `action_prev`, `action_next`, `author_email`, `mkdate`, `chdate`) VALUES
            ('55f3a548348dcbfdca67678588887ffd', 1, '', 'Dies ist Ihr persönlicher Arbeitsplatz in Stud.IP. Hier sind Funktionen, die früher unter \"Tools\" zu finden waren, sowie neue Werkzeuge gesammelt.', 'B', 0, '', 'dispatch.php/contents/overview', '', '', 'root@localhost', 1630577268, 1630577268),
            ('55f3a548348dcbfdca67678588887ffd', 2, '', 'Courseware dient zum Erstellen von Lerninhalten. Texte, Dateien, Videos, Tests und vieles mehr lassen sich einfach zu komplexen Lerninhalten kombinieren.', 'RT', 0, '#content UL:eq(0)  LI:eq(0)  A:eq(0)  DIV:eq(0)  IMG:eq(0)', 'dispatch.php/contents/overview', '', '', 'dozent@studip.de', 1630577268, 1630577268),
            ('55f3a548348dcbfdca67678588887ffd', 3, '', 'Dateien zeigt Ihren persönlichen, geschützten Dateibereich sowie eine Übersichtsseite über alle Dateien, auf die Sie in Stud.IP Zugriff haben.', 'RT', 0, '#content UL:eq(0)  LI:eq(1)  A:eq(0)  DIV:eq(0)', 'dispatch.php/contents/overview', '', '', 'dozent@studip.de', 1630577268, 1630577268),
            ('55f3a548348dcbfdca67678588887ffd', 4, '', 'Ankündigungen ermöglicht das Einstellen von News auf Ihrer Profilseite, in Veranstaltungen in denen Sie lehren oder Einrichtungen, die Sie administrieren.', 'LT', 0, '#content UL:eq(0)  LI:eq(2)  A:eq(0)  DIV:eq(0)  IMG:eq(0)', 'dispatch.php/contents/overview', '', '', 'dozent@studip.de', 1630577268, 1630577268),
            ('55f3a548348dcbfdca67678588887ffd', 5, '', 'Fragebögen zeigt alle Fragebögen die Sie erstellt haben und die sich im persönlichen, Veranstaltungs- oder Einrichungskontext nutzen lassen.', 'LT', 0, '#content UL:eq(0)  LI:eq(3)  A:eq(0)  DIV:eq(0)  IMG:eq(0)', 'dispatch.php/contents/overview', '', '', 'dozent@studip.de', 1630577268, 1630577268),
            ('55f3a548348dcbfdca67678588887ffd', 6, '', 'Evaluationen bietet einen Baukasten zum Erstellen komplexer Umfragen sowie deren Nutzung im persönlichen, Veranstaltungs- oder Einrichungskontext.', 'T', 0, '#content UL:eq(0)  LI:eq(4)  A:eq(0)  DIV:eq(0)  IMG:eq(0)', 'dispatch.php/contents/overview', '', '', 'dozent@studip.de', 1630577268, 1630577268),
            ('55f3a548348dcbfdca67678588887ffd', 7, '', 'Lernmodule/ILIAS bietet je nach Standort Zugriff auf die Accountverwaltung von Plattformen und Werkzeugen, die an Stud.IP angedockt sind.', 'T', 0, '#content UL:eq(0)  LI:eq(5)  A:eq(0)  DIV:eq(0)  IMG:eq(0)', 'dispatch.php/contents/overview', '', '', 'dozent@studip.de', 1630577268, 1630577268),
            ('d9a066071e2be43b2b51c37a9d692026', 1, 'OER Campus', 'Der OER Campus ist neu in Stud.IP 5. Hier können Lernmaterialien gesucht, verwaltet und mit anderen geteilt werden.', 'B', 0, '', 'dispatch.php/oer/market', '', '', 'root@localhost', 1630577268, 1630577268),
            ('d9a066071e2be43b2b51c37a9d692026', 2, '', 'Lernmaterialien aus dem OER Campus können von hier aus direkt im Dateibereich einer Veranstaltung bereitgestellt werden.', 'B', 0, '', 'dispatch.php/oer/market', '', '', 'root@localhost', 1630577268, 1630577268),
            ('d9a066071e2be43b2b51c37a9d692026', 3, '', 'Um gezielt Lernmaterialien zu suchen, können Sie hier einen Suchbegriff eingeben.', 'B', 0, 'INPUT[name=search]', 'dispatch.php/oer/market', '', '', 'root@localhost', 1630577268, 1630577268),
            ('d9a066071e2be43b2b51c37a9d692026', 4, '', 'Über die Filterfunktion können Sie die angezeigten Lernmaterialien weiter eingrenzen.', 'B', 0, '#content FORM:eq(0)  DIV:eq(0)  DIV:eq(0)  DIV:eq(0)  BUTTON:eq(0)', 'dispatch.php/oer/market', '', '', 'root@localhost', 1630577268, 1630577268),
            ('d9a066071e2be43b2b51c37a9d692026', 5, '', 'Im Entdeckermodus finden Sie Lernmaterial nach Schlagwörtern und Themen geordnet.', 'L', 0, '#content FORM:eq(0)  DIV:eq(3)  DIV:eq(0)  DIV:eq(0)  DIV:eq(1)', 'dispatch.php/oer/market', '', '', 'root@localhost', 1630577268, 1630577268),
            ('d9a066071e2be43b2b51c37a9d692026', 6, '', 'Wenn Sie selbst Lernmaterial erstellt haben, das Sie anderen zur Verfügung stellen möchten, können Sie es hier hochladen.', 'R', 0, 'div.sidebar-widget A:eq(0)', 'dispatch.php/oer/market', '', '', 'root@localhost', 1630577268, 1630577268),
            ('d9a066071e2be43b2b51c37a9d692026', 7, '', 'Ihr hochgeladenes Lernmaterial können Sie im Bereich \"Meine Materialien\" verwalten. Nutzen Sie den Entwurfsmodus, wenn Sie Ihr Material noch nicht veröffentlichen wollen.', 'B', 0, '#nav_oer_mymaterial A:eq(0)', 'dispatch.php/oer/market', '', '', 'root@localhost', 1630577268, 1630577268),
            ('dac47ec2e8a848744bde4b3881d31553', 1, 'Möchten Sie sehen was in Stud.IP 5 neu ist?', '', 'B', 0, '', 'dispatch.php/start', '', '', 'root@localhost', 1630577268, 1630577268),
            ('dac47ec2e8a848744bde4b3881d31553', 2, '', 'Der OER Campus ermöglicht das Teilen und Finden von freien Lehr- und Lernmaterialien.', 'B', 0, '#nav_oer A:eq(0)  IMG:eq(0)', 'dispatch.php/start', '', '', 'dozent@studip.de', 1630577268, 1630577268),
            ('dac47ec2e8a848744bde4b3881d31553', 3, '', '\"Mein Arbeitsplatz\" ersetzt und erweitert den Punkt \"Tools\" der früheren Stud.IP-Versionen. Hier finden Sie jetzt Ihre Fragebögen, Ankündigungen, Evaluationen und Dateien sowie Courseware und ggf. Zugang zu ILIAS.', 'B', 0, '#nav_contents A:eq(0)  IMG:eq(0)', 'dispatch.php/start', '', '', 'root@localhost', 1630577268, 1630577268),
            ('dac47ec2e8a848744bde4b3881d31553', 4, '', 'Sie können sich Ihre belegten Veranstaltungen jetzt auch in einer übersichtlichen Kacheldarstellung anzeigen lassen. Die ist besonders gut für Smartphones geeignet.', 'B', 0, '.sidebar-views:eq(1)', 'dispatch.php/my_courses', '', '', 'dozent@studip.de', 1630577268, 1630577268),
            ('dac47ec2e8a848744bde4b3881d31553', 5, '', 'In Veranstaltungen finden Sie Optionen für Literatur nun im Dateibereich. Nachrichten lassen sich gleichzeitig  an mehrere Gruppen versenden.', 'B', 0, '', 'dispatch.php/my_courses', '', '', 'root@localhost', 1630577268, 1630577268),
            ('dac47ec2e8a848744bde4b3881d31553', 6, '', 'Das waren nur die wichtigsten Änderungen. Stud.IP 5 enthält noch zahlreiche weitere Verbesserungen, die Ihnen die Arbeit hoffentlich etwas erleichtern. Schreiben Sie Feedback gerne an die Entwicklungscommunity unter feedback@studip.de', 'B', 0, '', 'dispatch.php/start', '', '', 'root@localhost', 1630577268, 1630577268),
            ('a848744bde4dac47ec2e8b383155381d', 1, 'Möchten Sie sehen was in Stud.IP 5 neu ist?', '', 'B', 0, '', 'dispatch.php/start', '', '', 'root@localhost', 1630577268, 1630577268),
            ('a848744bde4dac47ec2e8b383155381d', 2, '', 'Der OER Campus ermöglicht das Teilen und Finden von freien Lehr- und Lernmaterialien.', 'B', 0, '#nav_oer A:eq(0)  IMG:eq(0)', 'dispatch.php/start', '', '', 'dozent@studip.de', 1630577268, 1630577268),
            ('a848744bde4dac47ec2e8b383155381d', 3, '', '\"Mein Arbeitsplatz\" ersetzt und erweitert den Punkt \"Tools\" der früheren Stud.IP-Versionen. Hier finden Sie jetzt Ihre Fragebögen, Ankündigungen, Evaluationen und Dateien sowie Courseware und ggf. Zugang zu ILIAS.', 'B', 0, '#nav_contents A:eq(0)  IMG:eq(0)', 'dispatch.php/start', '', '', 'root@localhost', 1630577268, 1630577268),
            ('a848744bde4dac47ec2e8b383155381d', 4, '', 'Sie können sich Ihre belegten Veranstaltungen jetzt auch in einer übersichtlichen Kacheldarstellung anzeigen lassen. Die ist besonders gut für Smartphones geeignet.', 'B', 0, '.sidebar-views:eq(1)', 'dispatch.php/my_courses', '', '', 'dozent@studip.de', 1630577268, 1630577268),
            ('a848744bde4dac47ec2e8b383155381d', 5, '', 'In Veranstaltungen finden Sie Optionen für Literatur nun im Dateibereich. Nachrichten lassen sich gleichzeitig  an mehrere Gruppen versenden.', 'B', 0, '', 'dispatch.php/my_courses', '', '', 'root@localhost', 1630577268, 1630577268),
            ('a848744bde4dac47ec2e8b383155381d', 6, '', 'Über die Terminvergabe können Sie freie Termine auf ihrer Profilseite veröffentlichen, die dann von anderen gebucht werden können, z.B. als Sprechstunde. Falls unterstützt, können Sie zu jedem Termin Videofunktionen einschalten.', 'B', 0, '', 'dispatch.php/profilemodules', '', '', 'dozent@studip.de', 1630577268, 1630577268),
            ('a848744bde4dac47ec2e8b383155381d', 7, '', 'Das waren nur die wichtigsten Änderungen. Stud.IP 5 enthält noch zahlreiche weitere Verbesserungen, die Ihnen die Arbeit hoffentlich etwas erleichtern. Schreiben Sie Feedback gerne an die Entwicklungscommunity unter feedback@studip.de', 'B', 0, '', 'dispatch.php/start', '', '', 'root@localhost', 1630577268, 1630577268)
        ");

        $tour_id = '21f487fa74e3bfc7789886f40fe4131a';
        $tour = "(:tour_id, :tour_id, 'Forum nutzen', 'Die Inhalte dieser Tour stammen aus der alten Tour des Forums (Sidebar > Aktionen > Tour starten).', 'tour', 'autor,tutor,dozent,admin,root', 1, 'de', '5.0', '', '', 1405415746, 0)";
        $settings = "(:tour_id, 1, 'standard', NULL, NULL)";
        $steps = "(:tour_id, 1, 'Forum', 'Diese Tour gibt einen Überblick über die Elemente und Interaktionsmöglichkeiten des Forums.\r\n\r\nUm zum nächsten Schritt zu gelangen, klicken Sie bitte rechts unten auf \"Weiter\".', 'BL', 0, '', 'plugins.php/coreforum', '', '', '', 1405415772, 0),
        (:tour_id, 2, 'Sie befinden sich hier:...', 'An dieser Stelle wird angezeigt, welcher Bereich des Forums gerade betrachtet wird.', 'BL', 0, 'DIV#tutorBreadcrumb', 'plugins.php/coreforum', '', '', '', 1405415875, 0),
        (:tour_id, 3, 'Kategorie', 'Das Forum ist unterteilt in Kategorien, Themen und Beiträge. Eine Kategorie fasst Forumsbereiche in größere Sinneinheiten zusammen.', 'BL', 0, '#tutorCategory', 'plugins.php/coreforum', '', '', '', 1405416611, 0),
        (:tour_id, 4, 'Bereich', 'Das ist ein Bereich innerhalb einer Kategorie. Bereiche beinhalten die Diskussionstränge. Bereiche können mit per drag & drop in ihrer Reihenfolge verschoben werden.', 'BL', 0, '#sortable_areas TABLE:eq(0)  TBODY:eq(0)  TR:eq(0)  TD:eq(1)  DIV:eq(0)  SPAN:eq(0)  A:eq(0)  SPAN:eq(0)', 'plugins.php/coreforum', '', '', '', 1405416664, 0),
        (:tour_id, 5, 'Info-Icon', 'Dieses Icon färbt sich rot, sobald es etwas neues in diesem Bereich gibt.', 'B', 0, '#sortable_areas TABLE:eq(0)  TBODY:eq(0)  TR:eq(0)  TD:eq(0)  A:eq(0)  IMG:eq(0)', 'plugins.php/coreforum', '', '', '', 1405416705, 0),
        (:tour_id, 6, 'Suchen', 'Hier können sämtliche Inhalte dieses Forums durchsucht werden.\r\nUnterstützt werden auch Mehrwortsuchen. Außerdem kann die Suche auf eine beliebige Kombination aus Titel, Inhalt und Autor eingeschränkt werden.', 'BL', 0, '#tutorSearchInfobox DIV:eq(0)', 'plugins.php/coreforum', '', '', '', 1405417134, 0),
        (:tour_id, 7, 'Forum abonnieren', 'Das gesamte Forum, oder einzelne Themen können abonniert werden. Dann wird bei jedem neuen Beitrag in diesem Forum eine Benachrichtigung angezeigt und eine Nachricht versendet.', 'RT', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(9)  DIV:eq(0)', 'plugins.php/coreforum', '', '', 'dozent@studip.de', 1405416795, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = '89786eac42f52ac316790825b4f5c0b2';
        $tour = "(:tour_id, :tour_id, 'Use Forum', 'The content of this tour is from the old tour of the forum (Sidebar > actions > start tour).', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '5.0', '', '', 1405415746, 0)";
        $settings = "(:tour_id, 1, 'standard', NULL, NULL)";
        $steps = "(:tour_id, 1, 'Forum', 'This tour provides an overview of the forum\'s elements and options of interaction.\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.', 'BL', 0, '', 'plugins.php/coreforum', '', '', '', 1405415772, 0),
        (:tour_id, 2, 'You are here:...', 'Here you can see which sector of the forum you are currently looking at.', 'BL', 0, 'DIV#tutorBreadcrumb', 'plugins.php/coreforum', '', '', '', 1405415875, 0),
        (:tour_id, 3, 'Category', 'The forum is divided into categories, topics and posts. A category summarises forum areas into larger units of meaning.', 'BL', 0, '#tutorCategory', 'plugins.php/coreforum', '', '', '', 1405416611, 0),
        (:tour_id, 4, 'Area', 'This is an area within a category. Areas contain threads. The order of areas can be altered using drag&drop', 'BL', 0, '#sortable_areas TABLE:eq(0)  TBODY:eq(0)  TR:eq(0)  TD:eq(1)  DIV:eq(0)  SPAN:eq(0)  A:eq(0)  SPAN:eq(0)', 'plugins.php/coreforum', '', '', '', 1405416664, 0),
        (:tour_id, 5, 'Info-Icon', 'This icon turns red as soon as there is something new in this sector.', 'B', 0, '#sortable_areas TABLE:eq(0)  TBODY:eq(0)  TR:eq(0)  TD:eq(0)  A:eq(0)  IMG:eq(0)', 'plugins.php/coreforum', '', '', '', 1405416705, 0),
        (:tour_id, 6, 'Search', 'All contents of this forum can be browsed here. Multiple word searches are also supported. In addition, the search can be limited to any combination of title, content and author.', 'BL', 0, '#tutorSearchInfobox DIV:eq(0)', 'plugins.php/coreforum', '', '', '', 1405417134, 0),
        (:tour_id, 7, 'Subscribe to forum', 'You can subscribe to the whole forum or individual topics . In this case a notification will be generated and you receive a meassage for each new post in this forum.', 'RT', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(9)  DIV:eq(0)', 'plugins.php/coreforum', '', '', 'dozent@studip.de', 1405416795, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = '44f859c50648d3410c39207048ddd833';
        $tour = "(:tour_id, :tour_id, 'Forum verwalten', 'Die Inhalte dieser Tour stammen aus der alten Tour des Forums (Sidebar > Aktionen > Tour starten).', 'tour', 'tutor,dozent,admin,root', 1, 'de', '5.0', '', 'root@localhost', 1405417901, 0)";
        $settings = "(:tour_id, 1, 'standard', NULL, 1631619331)";
        $steps = "(:tour_id, 1, 'Forum verwalten', 'Sie haben die Möglichkeit sich eine Tour zur Verwaltung des Forums anzuschauen.\r\n\r\nUm die Tour zu beginnen, klicken Sie bitte unten rechts auf \"Weiter\".', 'TL', 0, '', 'plugins.php/coreforum', '', '', '', 1405418008, 0),
        (:tour_id, 2, 'Kategorie bearbeiten', 'Mit diesen Icons kann der Name der Kategorie geändert oder aber die gesamte Kategorie gelöscht werden. Die Bereiche werden in diesem Fall in die Kategorie \"Allgemein\" verschoben und bleiben somit erhalten.\r\n\r\nDie Kategorie \"Allgemein\" kann nicht gelöscht werden und ist daher in jedem Forum enthalten.', 'L', 0, '#forum #sortable_areas TABLE CAPTION #tutorCategoryIcons', 'plugins.php/coreforum', '', '', 'dozent@studip.de', 1405424216, 0),
        (:tour_id, 3, 'Bereich bearbeiten', 'Wird der Mauszeiger auf einem Bereich positioniert, erscheinen Aktions-Icons.\r\nMit diesen Icons kann der Name und die Beschreibung eines Bereiches geändert oder auch der gesamte Bereich gelöscht werden.\r\nDas Löschen eines Bereichs, führt dazu, dass alle enthaltenen Themen gelöscht werden.', 'L', 0, '#sortable_areas TABLE:eq(0)  TBODY:eq(0)  TR:eq(0)  TD:eq(4)', 'plugins.php/coreforum', '', '', 'dozent@studip.de', 1405424346, 0),
        (:tour_id, 4, 'Bereiche sortieren', 'Mit dieser schraffierten Fläche können Bereiche an einer beliebigen Stelle durch Klicken-und-Ziehen einsortiert werden. Dies kann einerseits dazu verwendet werden, um Bereiche innerhalb einer Kategorie zu sortieren, andererseits können Bereiche in andere Kategorien verschoben werden.', 'R', 0, '#sortable_areas TABLE:eq(0)  TBODY:eq(0)  TR:eq(0)  TD:eq(0)  IMG:eq(0)', 'plugins.php/coreforum', '', '', 'dozent@studip.de', 1405424379, 0),
        (:tour_id, 5, 'Neuen Bereich hinzufügen', 'Hier können neue Bereiche zu einer Kategorie hinzugefügt werden.', 'BR', 0, 'TFOOT TR TD A SPAN', 'plugins.php/coreforum', '', '', '', 1405424421, 0),
        (:tour_id, 6, 'Neue Kategorie erstellen', 'Hier kann eine neue Kategorie im Forum erstellt werden. Geben Sie hierfür den Titel der neuen Kategorie ein.', 'TL', 0, '#tutorAddCategory FIELDSET:eq(0)  LEGEND:eq(0)', 'plugins.php/coreforum', '', '', '', 1405424458, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = '3dbe7099f82dcdbba4580acb1105a0d6';
        $tour = "(:tour_id, :tour_id, 'Administering the forum', 'The administration of the forum is explained in this tour.', 'tour', 'tutor,dozent,admin,root', 1, 'en', '5.0', '', 'root@localhost', 1405417901, 1631619331)";
        $settings = "(:tour_id, 1, 'standard', NULL, 1631619331)";
        $steps = "(:tour_id, 1, 'Administering the forum', 'This tour provides an overview of the forum\'s administration.\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.', 'TL', 0, '', 'plugins.php/coreforum', '', '', '', 1405418008, 0),
        (:tour_id, 2, 'Edit category', 'The name of the category can be changed or, however, the whole category deleted with these icons. The sectors will in this case be shifted into the category \"General\" and are thus retained.\n\nThe category \"General\" cannot be deleted and is therefore included in each forum.', 'L', 0, '#forum #sortable_areas TABLE CAPTION #tutorCategoryIcons', 'plugins.php/coreforum', '', '', 'dozent@studip.de', 1405424216, 0),
        (:tour_id, 3, 'Edit area', 'Action icons will appear, if the cursor is positioned on an area\n\nYou can use the icons to change the name and description of an area, or to delete the whole area.\nThe deletion of an area causes all contained topics to be deleted.', 'L', 0, '#sortable_areas TABLE:eq(0)  TBODY:eq(0)  TR:eq(0)  TD:eq(4)', 'plugins.php/coreforum', '', '', 'dozent@studip.de', 1405424346, 0),
        (:tour_id, 4, 'Sort area', 'With this hatched surface areas can be sorted in at any place by clicking and dragging. This can, on one hand, be used in order to sort areas within a category, and on the other hand, areas can be shifted into other categories.', 'R', 0, '#sortable_areas TABLE:eq(0)  TBODY:eq(0)  TR:eq(0)  TD:eq(0)  IMG:eq(0)', 'plugins.php/coreforum', '', '', 'dozent@studip.de', 1405424379, 0),
        (:tour_id, 5, 'Add new area', 'New areas can be added to a category here.', 'BR', 0, 'TFOOT TR TD A SPAN', 'plugins.php/coreforum', '', '', '', 1405424421, 0),
        (:tour_id, 6, 'Create new category', 'A new category in the forum can be created here. Enter the title of the new category for this purpose.', 'TL', 0, '#tutorAddCategory FIELDSET:eq(0)  LEGEND:eq(0)', 'plugins.php/coreforum', '', '', '', 1405424458, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = '49604a77654617a745e29ad6b253e491';
        $tour = "(:tour_id, :tour_id, 'Gestaltung der Startseite', 'In dieser Tour werden die Funktionen und Gestaltungsmöglichkeiten der Startseite vorgestellt.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'de', '5.0', '', 'root@localhost', 1405934780, 1631613451)";
        $settings = "(:tour_id, 1,'standard', NULL, 1631613451)";
        $steps = "(:tour_id, 1, 'Funktionen und Gestaltungsmöglichkeiten der Startseite', 'Diese Tour gibt Ihnen einen Überblick über die wichtigsten Funktionen der \"Startseite\".\r\n\r\nUm auf den nächsten Schritt zu kommen, klicken Sie bitte rechts unten auf \"Weiter\".', 'B', 0, '', 'dispatch.php/start', '', '', 'root@localhost', 1405934926, 0),
        (:tour_id, 2, 'Individuelle Gestaltung der Startseite', 'Die Startseite ist standardmäßig so konfiguriert, dass die Elemente \"Schnellzugriff\", \"Ankündigungen\", \"Meine aktuellen Termine\" und  \"Umfragen\" angezeigt werden. Die Elemente werden Widgets genannt und  können entfernt, hinzugefügt und verschoben werde.n Jedes Widget kann individuell hinzugefügt, entfernt und verschoben werden.', 'TL', 0, '', 'dispatch.php/start', '', '', '', 1405934970, 0),
        (:tour_id, 3, 'Widget hinzufügen', 'Hier können Widgets hinzugefügt werden. Zusätzlich zu den Standard-Widgets kann beispielsweise der persönliche Stundenplan auf der Startseite anzeigt werden. Neu hinzugefügte Widgets erscheinen ganz unten auf der Startseite. Darüber hinaus kann in der Sidebar direkt zu jedem Widget gesprungen werden.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(5)  DIV:eq(0)', 'dispatch.php/start', '', '', '', 1405935192, 0),
        (:tour_id, 4, 'Sprungmarken', 'Darüber hinaus kann mit Sprungmarken direkt zu jedem Widget gesprungen werden.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(2)  DIV:eq(0)', 'dispatch.php/start', '', '', '', 1406623464, 0),
        (:tour_id, 5, 'Widget positionieren', 'Ein Widget kann per Drag&Drop an die gewünschte Position verschoben werden: Dazu wird in die Titelzeile eines Widgets geklickt, die Maustaste gedrückt gehalten und das Widget an die gewünschte Position gezogen.', 'B', 0, '.widget-header', 'dispatch.php/start', '', '', '', 1405935687, 0),
        (:tour_id, 6, 'Widget bearbeiten', 'Bei einigen Widgets wird neben dem X zum Schließen noch ein weiteres Symbol angezeigt. Der Schnellzugriff bspw. kann durch Klick auf diesen Button individuell angepasst, die Ankündigungen können abonniert und bei den aktuellen Terminen bzw. Stundenplan können Termine hinzugefügt werden.', 'L', 0, '#widget-8', 'dispatch.php/start', '', '', '', 1405935792, 0),
        (:tour_id, 7, 'Widget entfernen', 'Jedes Widget kann durch Klicken auf das X in der rechten oberen Ecke entfernt werden. Bei Bedarf kann es jederzeit wieder hinzugefügt werden.', 'R', 0, '.widget-header', 'dispatch.php/start', '', '', '', 1405935376, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = 'f0aeb0f6c4da3bd61f48b445d9b30dc1';
        $tour = "(:tour_id, :tour_id, 'Design of the start page', 'The functions and design possibilities of the start page are presented in this feature tour.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '5.0', '', 'root@localhost', 1405934780, 1631613451)";
        $settings = "(:tour_id, 1,'standard', NULL, 1631613451)";
        $steps = "(:tour_id, 1, 'Functions and design possibilities of the start page', 'This tour provides an overview of the start page\'s features and functions.\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.', 'B', 0, '', 'dispatch.php/start', '', '', 'root@localhost', 1405934926, 0),
        (:tour_id, 2, 'Individual design of the start page', 'The default configuration of the start page is that the elements \"Quicklinks\", \"announcements\", \"my current appointments\" and  \"surveys\" are displayed. The elements are called widgets and  can be deleted, added and moved. Each widget can be individually added, deleted and moved.', 'TL', 0, '', 'dispatch.php/start', '', '', '', 1405934970, 0),
        (:tour_id, 3, 'Add widget', 'Widgets can be added here. In addition to the standard widgets the personal timetable can, for example, be displayed on the start page. Newly added widgets appear right at the bottom on the start page. In addition, it is possible to jump directly to each widget in the sidebar.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(5)  DIV:eq(0)', 'dispatch.php/start', '', '', '', 1405935192, 0),
        (:tour_id, 4, 'Jump labels', 'In addition, it is possible to jump directly to each widget using jump labels.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(2)  DIV:eq(0)', 'dispatch.php/start', '', '', '', 1406623464, 0),
        (:tour_id, 5, 'Position widget', 'A widget can be moved to the desired position using drag&drop: For this purpose you click into the headline of a widget, hold down the mouse button, and drag the widget to the desired position.', 'B', 0, '.widget-header', 'dispatch.php/start', '', '', '', 1405935687, 0),
        (:tour_id, 6, 'Edit widget', 'With several widgets a further symbol is displayed in addition to the X for closing. The widget \"Quicklinks\", for example, can be adjusted individually by clicking on this button, the announcements can be subscribed to and appointments can be added with the actual appointments or timetable.', 'L', 0, '#widget-8', 'dispatch.php/start', '', '', '', 1405935792, 0),
        (:tour_id, 7, 'Remove widget', 'Each widget can be removed by clicking on the X in the right upper corner. If required, it can be added again at all times.', 'R', 0, '.widget-header', 'dispatch.php/start', '', '', '', 1405935376, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = '4d41c9760a3248313236af202275107c';
        $tour = "('', :tour_id, 'Lesen im Wiki', 'Die Tour erklärt die verschiedenen Anzeige-Modalitäten zum Lesen des Wikis.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'de', '5.0', '', 'root@localhost', 1441276241, 1631619264)";
        $settings = "(:tour_id, 1,'standard', NULL, 1631619264)";
        $steps = "(:tour_id, 1, 'Lesen im Wiki', 'Diese Tour gibt einen Überblick über die Anzeige von Wiki-Seiten.\r\n\r\nUm zum nächsten Schritt zu gelangen, klicken Sie bitte rechts unten auf \"Weiter\".', 'T', 0, '', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 2, 'Wiki-Startseite', 'Zeigt die Basis-Seite des Wikis an.', 'R', 0, '#nav_wiki_show', 'wiki.php', '', '', 'dozent@studip.de', 1441276241, 0),
        (:tour_id, 3, 'Neue Seiten', 'Zeigt eine tabellarische Übersicht neu erstellter und neu bearbeiteter Wiki-Seiten an.', 'R', 0, '#nav_wiki_listnew', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 4, 'Alle Seiten', 'Zeigt eine tabellarische Übersicht aller Wiki-Seiten an.', 'R', 0, '#nav_wiki_listall', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 5, 'Ansichten', 'Wenn eine Textänderung in einer Wiki-Seite vorgenommen wurde, stehen drei Anzeigemodi zur Auswahl:\r\n- Standard: Ohne Zusatzinformation\r\n- Textänderungen anzeigen: Welche Textpassagen wurden geändert?\r\n- Text mit AutorInnenzuordnung anzeigen: Wer hat hat etwas geändert?', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(16)  DIV:eq(0)', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 6, 'Suche', 'Zeigt die Wiki-Seiten an, in denen der eingegebene Suchbegriff vorkommt. Die Suche steht nur in der Standard-Ansicht zur Verfügung.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(13)  DIV:eq(0)', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 7, 'Kommentare', 'Stellt verschiedene Modalitäten zur Anzeige von Kommentaren bereit, die in einer Wiki-Seite eingetragen wurden.', 'R', 0, '#link-76d39424649110401006432124ea88a2 A:eq(0)', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 8, 'Kommentare einblenden', 'Alle Kommentare werden als Textblock an der Textposition angezeigt, an der sie in die Wiki-Seite eingefügt wurden.', 'R', 0, '#link-76d39424649110401006432124ea88a2 A:eq(0)', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 9, 'Kommentare ausblenden', 'Die in einer Wiki-Seite eingefügten Kommentare werden nicht angezeigt.', 'R', 0, '#link-b7e236be73b877f765813669fba3d56e A:eq(0)', 'wiki.php', '', '', '', 1441276241, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = '5d41c9760a3248313236af202275107c';
        $tour = "('', :tour_id, 'Reading the Wiki', 'This tour provides help for reading Wiki pages.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '5.0', '', 'root@localhost', 1441276241, 1631619264)";
        $settings = "(:tour_id, 1,'standard', NULL, 1631619264)";
        $steps = "(:tour_id, 1, 'Reading the Wiki', 'This tour gives a general overview of the different modes to read Wiki pages.\r\n\r\nTo proceed, please click \"Continue\" on the lower-right button.', 'T', 0, '', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 2, 'WikiWikiWeb', 'Displays the basic Wiki page, which is the foundation of all further Wiki pages.', 'R', 0, '#nav_wiki_show', 'wiki.php', '', '', 'dozent@studip.de', 1441276241, 0),
        (:tour_id, 3, 'New pages', 'Displays a survey of all recently created or edited Wiki pages in table form.', 'R', 0, '#nav_wiki_listnew', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 4, 'All pages', 'Displays a survey of all Wiki pages in table form.', 'R', 0, '#nav_wiki_listall', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 5, 'Views', 'If a Wiki page has been edited, the user may choose between three modes of viewing content:\r\n- Standard: Without extra information\r\n- Show text changes: Which parts of text have been edited?\r\n- Show text changes and associated author: Who was editing a part of text?', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(16)  DIV:eq(0)', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 6, 'Search', 'Shows all Wiki pages which contain the entered search term. The search is supported in Standard-View only.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(13)  DIV:eq(0)', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 7, 'Comments', 'Supports three modes of showing comments added to a Wiki page.', 'R', 0, '#link-76d39424649110401006432124ea88a2 A:eq(0)', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 8, 'Show comments', 'All comments are shown as a block of text exactly in that position, in which they were added.', 'R', 0, '#link-76d39424649110401006432124ea88a2 A:eq(0)', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 9, 'Hide comments', 'All added comments are hidden while displaying a page.', 'R', 0, '#link-b7e236be73b877f765813669fba3d56e A:eq(0)', 'wiki.php', '', '', '', 1441276241, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = '154e711257d4d32d865fb8f5fb70ad72';
        $tour = "(:tour_id, :tour_id, 'Meine Dateien', 'In dieser Tour wird der persönliche Dateibereich vorgestellt.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'de', '5.0', '', '', 1405592618, 0)";
        $settings = "(:tour_id, 1, 'standard', NULL, NULL)";
        $steps = "(:tour_id, 1, 'Dateien', 'Dies ist der persönliche Dateibereich. Hier können Dateien in Stud.IP gespeichert werden, um sie von dort auf andere Rechner herunterladen zu können.\n\nAndere Studierende oder Dozierende erhalten keinen Zugriff auf Dateien, die in den persönlichen Dateibereich hochgeladen werden.\n\nUm auf den nächsten Schritt zu kommen, klicken Sie bitte rechts unten auf \"Weiter\".', 'B', 0, '', 'dispatch.php/files', '', '', 'root@localhost', 1405592884, 0),
        (:tour_id, 2, 'Neue Dateien und Verzeichnisse', 'Hier können neue Dateien von dem Computer in den persönlichen Dateibereich hochgeladen und neue Verzeichnisse erstellt werden.', 'TL', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(8)  DIV:eq(0)', 'dispatch.php/files', '', '', '', 1405593409, 0),
        (:tour_id, 3, 'Dateiübersicht', 'Alle Dateien und Verzeichnisse werden tabellarisch aufgelistet. Neben dem Namen werden noch weitere Informationen wie der Dateityp oder die Dateigröße angezeigt.', 'TL', 0, '#files_table_form TABLE:eq(0)  CAPTION:eq(0)  DIV:eq(0)', 'dispatch.php/files', '', '', '', 1405593089, 0),
        (:tour_id, 4, '', 'Bereits hochgeladene Dateien und Ordner können hier bearbeitet, heruntergeladen, verschoben, kopiert und gelöscht werden.', 'TL', 0, 'table.documents tfoot .footer-items', 'dispatch.php/files', '', '', '', 1405594079, 0),
        (:tour_id, 5, 'Export', 'Hier besteht die Möglichkeit einzelne Ordner oder den vollständigen Dateibereich als ZIP-Datei herunterzuladen. Darin sind alle Dateien und Verzeichnisse enthalten.', 'LT', 0, 'table.documents .action-menu-icon', 'dispatch.php/files', '', '', 'dozent@studip.de', 1405593708, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = '05434e40601a9a2a7f5fa8208ae148c1';
        $tour = "(:tour_id, :tour_id, 'My documents', 'The personal document area will be presented in this tour.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '5.0', '', '', 1405592618, 0)";
        $settings = "(:tour_id, 1, 'standard', NULL, NULL)";
        $steps = "(:tour_id, 1, 'My documents', 'This tour provides an overview of the personal document manager.\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.', 'B', 0, '', 'dispatch.php/files', '', '', 'root@localhost', 1405592884, 0),
        (:tour_id, 2,  'New documents and indices', 'New documents can be uploaded from the computer into the personal document area and new indices can be created here.', 'TL', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(8)  DIV:eq(0)', 'dispatch.php/files', '', '', '', 1405593409, 0),
        (:tour_id, 3,  'Document overview', 'All documents and indices are listed in a tabular form. In addition to the name even more information is displayed such as the document type or the document size.', 'TL', 0, '#files_table_form TABLE:eq(0)  CAPTION:eq(0)  DIV:eq(0)', 'dispatch.php/files', '', '', '', 1405593089, 0),
        (:tour_id, 4, '', 'Already uploaded documents and folders can be edited, downloaded, shifted, copied and deleted here.', 'TL', 0, 'table.documents tfoot .footer-items', 'dispatch.php/files', '', '', '', 1405594079, 0),
        (:tour_id, 5, 'Export', 'Here you have the possibility to download individual folders or the full document area as a ZIP document. All documents and indices are contained therein.', 'LT', 0, 'table.documents .action-menu-icon', 'dispatch.php/files', '', '', 'dozent@studip.de', 1405593708, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = '7cccbe3b22dfa745c17cb776fb04537c';
        $tour = "(:tour_id, :tour_id, 'Meine Veranstaltungen (Dozierende)', 'In dieser Tour werden die wichtigsten Funktionen der Seite \"Meine Veranstaltungen\" vorgestellt.', 'tour', 'tutor,dozent,admin,root', 1, 'de', '5.0', '', '', 1406125685, 0)";
        $settings = "(:tour_id, 1, 'standard', NULL, NULL)";
        $steps = "(:tour_id, 1, 'Meine Veranstaltungen', 'Diese Tour gibt einen Überblick über die wichtigsten Funktionen der Seite \"Meine Veranstaltungen\".\r\n\r\nUm auf den nächsten Schritt zu kommen, klicken Sie bitte rechts unten auf \"Weiter\".', 'TL', 0, '', 'dispatch.php/my_courses', '', '', '', 1406125847, 0),
        (:tour_id, 2, 'Veranstaltungsüberblick', 'Hier werden die  Veranstaltungen des aktuellen und vergangenen Semesters angezeigt. Neue Veranstaltungen erscheinen zunächst in rot.', 'BL', 0, '#my_seminars TABLE:eq(0)  CAPTION:eq(0)', 'dispatch.php/my_courses', '', '', 'dozent@studip.de', 1406125908, 0),
        (:tour_id, 3, 'Veranstaltungsdetails', 'Mit Klick auf das \"i\" erscheint ein Fenster mit den wichtigsten Eckdaten der Veranstaltung.', 'BR', 0, '#my_seminars .action-menu-icon', 'dispatch.php/my_courses', '', '#my_seminars TABLE:eq(0)  TBODY:eq(0)  TR:eq(1)  TD:eq(5)  NAV:eq(0)', 'dozent@studip.de', 1406125992, 0),
        (:tour_id, 4, 'Veranstaltungsinhalte', 'Hier werden alle Inhalte (wie z.B. ein Forum) durch entsprechende Symbole angezeigt.\r\nFalls es seit dem letzten Login Neuigkeiten gab, erscheinen diese in rot.', 'B', 0, '#my_seminars .my-courses-navigation-item', 'dispatch.php/my_courses', '', '', 'dozent@studip.de', 1406126049, 0),
        (:tour_id, 5, 'Bearbeitung oder Löschung einer Veranstaltung', 'Der Klick auf das Zahnrad ermöglicht die Bearbeitung einer Veranstaltung.\r\nFalls bei einer Veranstaltung Teilnehmerstatus besteht, kann hier eine Austragung, durch Klick auf das Tür-Icon, vorgenommen werden.', 'BR', 0, '#my_seminars .action-menu-icon', 'dispatch.php/my_courses', '', '', 'dozent@studip.de', 1406126134, 0),
        (:tour_id, 6, 'Anpassung der Veranstaltungsansicht', 'Zur Anpassung der Veranstaltungsübersicht, kann man die Veranstaltungen nach bestimmten Kriterien (wie z.B. Studienbereiche, Lehrende oder Farben) gliedern.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(11)  DIV:eq(0)', 'dispatch.php/my_courses', '', '', '', 1406126281, 0),
        (:tour_id, 7, 'Zugriff auf Veranstaltung vergangener und zukünftiger Semester', 'Durch Klick auf das Drop-Down Menü können beispielsweise Veranstaltung aus vergangenen Semestern angezeigt werden.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(5)  DIV:eq(1)', 'dispatch.php/my_courses', '', '', '', 1406126316, 0),
        (:tour_id, 8, 'Weitere mögliche Aktionen', 'Hier können Sie alle Neuigkeiten als gelesen markieren, Farbgruppierungen nach Belieben ändern oder\r\nauch die Benachrichtigungen über Aktivitäten in den einzelnen Veranstaltungen anpassen.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(8)  DIV:eq(0)', 'dispatch.php/my_courses', '', '', '', 1406126374, 0),
        (:tour_id, 9, 'Studiengruppen und Einrichtungen', 'Es besteht zudem die Möglichkeit auf persönliche Studiengruppen oder Einrichtungen zuzugreifen.', 'R', 0, '#nav_browse_my_institutes A', 'dispatch.php/my_courses', '', '', '', 1406126415, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = '1badcf28ab5b206d9150b2b9683b4cb6';
        $tour = "(:tour_id, :tour_id, 'My courses (lecturers)', 'The most important functions of the site \"My courses\" are presented in this tour.', 'tour', 'tutor,dozent,admin,root', 1, 'en', '5.0', '', '', 1406125685, 0)";
        $settings = "(:tour_id, 1, 'standard', NULL, NULL)";
        $steps = "(:tour_id, 1, 'My courses', 'This tour provides an overview of the functionality of \"My courses\".\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.', 'TL', 0, '', 'dispatch.php/my_courses', '', '', '', 1406125847, 0),
        (:tour_id, 2, 'Overview of courses', 'The courses of the current and past semester are displayed here. New courses initially appear in red.', 'BL', 0, '#my_seminars TABLE:eq(0)  CAPTION:eq(0)', 'dispatch.php/my_courses', '', '', 'dozent@studip.de', 1406125908, 0),
        (:tour_id, 3, 'Course details', 'With a click on the \"i\" a window appears with the most important facts of the courses.', 'BR', 0, '#my_seminars .action-menu-icon', 'dispatch.php/my_courses', '', '#my_seminars TABLE:eq(0)  TBODY:eq(0)  TR:eq(1)  TD:eq(5)  NAV:eq(0)', 'dozent@studip.de', 1406125992, 0),
        (:tour_id, 4, 'Course contents', 'All contents (such as e.g. a forum) are displayed by corresponding symbols here.\n\nIf there were any news since the last login these will appear in red.', 'B', 0, '#my_seminars .my-courses-navigation-item', 'dispatch.php/my_courses', '', '', 'dozent@studip.de', 1406126049, 0),
        (:tour_id, 5, 'Editing or deletion of a course', 'A click on the cog wheel enables you to edit a course.\n\nIf you have participant status in a course, you can sign out by clicking on the door icon.', 'BR', 0, '#my_seminars .action-menu-icon', 'dispatch.php/my_courses', '', '', 'dozent@studip.de', 1406126134, 0),
        (:tour_id, 6, 'Adjustment to the event view', 'In order to adjust the course overview you can order your courses according to certain criteria (such as e.g. fields of study, lecturers, or colours).', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(11)  DIV:eq(0)', 'dispatch.php/my_courses', '', '', '', 1406126281, 0),
        (:tour_id, 7, 'Access to an event of past and future semesters', 'For example, by clicking on the drop-down menu, courses from past semesters can be displayed.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(5)  DIV:eq(1)', 'dispatch.php/my_courses', '', '', '', 1406126316, 0),
        (:tour_id, 8, 'Further possible actions', 'Here you can mark all news as read, change colour groups as you please, and also adjust the notifications about activities in the individual courses.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(8)  DIV:eq(0)', 'dispatch.php/my_courses', '', '', '', 1406126374, 0),
        (:tour_id, 9, 'Study groups and facilities', 'There is moreover the possibility to access personal study groups or facilities.', 'R', 0, '#nav_browse_my_institutes A', 'dispatch.php/my_courses', '', '', '', 1406126415, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = 'b74f8459dce2437463096d56db7c73b9';
        $tour = "(:tour_id, :tour_id, 'Meine Veranstaltungen (Studierende)', 'In dieser Tour werden die wichtigsten Funktionen der Seite \"Meine Veranstaltungen\" vorgestellt.', 'tour', 'autor,admin,root', 1, 'de', '5.0', '', '', 1405521073, 0)";
        $settings = "(:tour_id, 1, 'standard', NULL, NULL)";
        $steps = "(:tour_id, 1, 'Meine Veranstaltungen', 'Diese Tour gibt einen Überblick über die wichtigsten Funktionen der Seite \"Meine Veranstaltungen\".\r\n\r\nUm auf den nächsten Schritt zu kommen, klicken Sie bitte rechts unten auf \"Weiter\".', 'TL', 0, '', 'dispatch.php/my_courses', '', '', '', 1405521184, 0),
        (:tour_id, 2, 'Veranstaltungsüberblick', 'Hier werden die  Veranstaltungen des aktuellen und vergangenen Semesters angezeigt. Neue Veranstaltungen erscheinen zunächst in rot.', 'BL', 0, '#my_seminars TABLE:eq(0)  CAPTION:eq(0)', 'dispatch.php/my_courses', '', '', 'autor@studip.de', 1405521244, 0),
        (:tour_id, 3, 'Veranstaltungsdetails', 'Mit Klick auf das \"i\" erscheint ein Fenster mit den wichtigsten Eckdaten der Veranstaltung.', 'L', 0, '#my_seminars .action-menu-icon', 'dispatch.php/my_courses', '', '', 'autor@studip.de', 1405931069, 0),
        (:tour_id, 4, 'Veranstaltungsinhalte', 'Hier werden alle Inhalte (wie z.B. ein Forum) durch entsprechende Symbole angezeigt.\r\nFalls es seit dem letzten Login Neuigkeiten gab, erscheinen diese in rot.', 'LT', 0, '#my_seminars .my-courses-navigation-item', 'dispatch.php/my_courses', '', '', '', 1405931225, 0),
        (:tour_id, 5, 'Verlassen der Veranstaltung', 'Ein Klick auf das Tür-Icon ermöglicht eine direkte Austragung aus der Veranstaltung.', 'L', 0, '#my_seminars .action-menu-icon', 'dispatch.php/my_courses', '', '', 'autor@studip.de', 1405931272, 0),
        (:tour_id, 6, 'Anpassung der Veranstaltungsansicht', 'Zur Anpassung der Veranstaltungsübersicht können die Veranstaltungen nach bestimmten Kriterien (wie z.B. Studienbereiche, Lehrende oder Farben) gruppiert werden.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(11)  DIV:eq(0)', 'dispatch.php/my_courses', '', '', '', 1405932131, 0),
        (:tour_id, 7, 'Zugriff auf Veranstaltung vergangener und zukünftiger Semester', 'Durch Klick auf das Drop-Down Menü können beispielsweise Veranstaltung aus vergangenen Semestern angezeigt werden.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(5)  DIV:eq(0)', 'dispatch.php/my_courses', '', '', '', 1405932230, 0),
        (:tour_id, 8, 'Weitere mögliche Aktionen', 'Hier können Sie alle Neuigkeiten als gelesen markieren, Farbgruppierungen nach Belieben ändern oder\r\nauch die Benachrichtigungen über Aktivitäten in den einzelnen Veranstaltungen anpassen.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(8)  DIV:eq(0)', 'dispatch.php/my_courses', '', '', '', 1405932320, 0),
        (:tour_id, 9, 'Studiengruppen und Einrichtungen', 'Es besteht zudem die Möglichkeit auf persönliche Studiengruppen oder Einrichtungen zuzugreifen.', 'R', 0, '#nav_browse_my_institutes A', 'dispatch.php/my_courses', '', '', '', 1405932519, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = 'fa963d2ca827b28e0082e98aafc88765';
        $tour = "(:tour_id, :tour_id, 'My courses (students)', 'The most important functions of the site \"My courses\" are presented in this tour.', 'tour', 'autor,admin,root', 1, 'en', '5.0', '', '', 1405521073, 0)";
        $settings = "(:tour_id, 1, 'standard', NULL, NULL)";
        $steps = "(:tour_id, 1, 'My courses', 'This tour provides an overview of the functionality of \"My courses\".\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.', 'TL', 0, '', 'dispatch.php/my_courses', '', '', '', 1405521184, 0),
        (:tour_id, 2, 'Overview of courses', 'The courses of the current and past semester are displayed here. New courses initially appear in red.', 'BL', 0, '#my_seminars TABLE:eq(0)  CAPTION:eq(0)', 'dispatch.php/my_courses', '', '', 'autor@studip.de', 1405521244, 0),
        (:tour_id, 3, 'Course details', 'With a click on the \"i\" a window appears with the most important benchmark data of the course.', 'L', 0, '#my_seminars .action-menu-icon', 'dispatch.php/my_courses', '', '', 'autor@studip.de', 1405931069, 0),
        (:tour_id, 4, 'Course contents', 'All contents (such as e.g. a forum) are displayed by corresponding symbols here.\n\nIf there were any news since the last login these will appear in red.', 'LT', 0, '#my_seminars .my-courses-navigation-item', 'dispatch.php/my_courses', '', '', '', 1405931225, 0),
        (:tour_id, 5, 'Leaving the course', 'A click on the door icon enables a direct removal from the course' , 'L', 0, '#my_seminars .action-menu-icon', 'dispatch.php/my_courses', '', '', 'autor@studip.de', 1405931272, 0),
        (:tour_id, 6, 'Adjustment to the course view', 'In order to adjust the course overview you can arrange your courses according to certain criteria (such as e.g. fields of study, lecturers or colours).', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(11)  DIV:eq(0)', 'dispatch.php/my_courses', '', '', '', 1405932131, 0),
        (:tour_id, 7, 'Access to an course of past and future semesters', 'By clicking on the drop-down menu courses from past semesters can be displayed for example.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(5)  DIV:eq(0)', 'dispatch.php/my_courses', '', '', '', 1405932230, 0),
        (:tour_id, 8, 'Further possible actions', 'Here you can mark all news as read, change colour groups as you please, or\n\nalso adjust the notifications about activities in the individual events.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(8)  DIV:eq(0)', 'dispatch.php/my_courses', '', '', '', 1405932320, 0),
        (:tour_id, 9, 'Study groups and institutes', 'There is moreover the possibility to access personal study groups or institutes.', 'R', 0, '#nav_browse_my_institutes A', 'dispatch.php/my_courses', '', '', '', 1405932519, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = '96ea422f286fb5bbf9e41beadb484a9a';
        $tour = "(:tour_id, :tour_id, 'Profilseite', 'In dieser Tour werden die Grundfunktionen und Bereiche der Profilseite vorgestellt.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'de', '5.0', '', 'root@localhost', 1406722657, 1631617118)";
        $settings = "(:tour_id, 1, 'standard', NULL, NULL)";
        $steps = "(:tour_id, 1, 'Profil-Tour', 'Diese Tour gibt Ihnen einen Überblick über die wichtigsten Funktionen des \"Profils\".\r\n\r\nUm auf den nächsten Schritt zu kommen, klicken Sie bitte rechts unten auf \"Weiter\".', 'T', 0, '', 'dispatch.php/profile', '', '', '', 1406722657, 0),
        (:tour_id, 2, 'Persönliches Bild', 'Wenn ein Bild hochgeladen wurde, wird es hier angezeigt. Dieses kann jederzeit geändert werden.', 'RT', 0, '.avatar-normal', 'dispatch.php/profile', '', '', '', 1406722657, 0),
        (:tour_id, 3, 'Stud.IP-Score', 'Der Stud.IP-Score wächst mit den Aktivitäten in Stud.IP und repräsentiert so die Erfahrung mit Stud.IP.', 'BL', 0, '#content TABLE:eq(0) TBODY:eq(0) TR:eq(0) TD:eq(0) A:eq(0)', 'dispatch.php/profile', '', '', '', 1406722657, 0),
        (:tour_id, 4, 'Ankündigungen', 'Sie können auf dieser Seite persönliche Ankündigungen veröffentlichen.', 'B', 0, '#content ARTICLE:eq(0)  HEADER:eq(0)  H1:eq(0)', 'dispatch.php/profile', '', '', '', 1406722657, 0),
        (:tour_id, 5, 'Neue Ankündigung', 'Klicken Sie auf das Plus-Zeichen, wenn Sie eine Ankündigung erstellen möchten.', 'BR', 0, '#content ARTICLE:eq(0)  HEADER:eq(0)  NAV:eq(0)  A:eq(0)', 'dispatch.php/profile', '', '', '', 1406722657, 0),
        (:tour_id, 6, 'Persönliche Angaben', 'Weitere persönliche Angaben und Einstellungen können über diese Seiten geändert werden.', 'B', 0, '#tabs li:eq(2)', 'dispatch.php/profile', '', '', 'dozent@studip.de', 1406722657, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = 'de1fbce508d01cbd257f9904ff8c3b43';
        $tour = "(:tour_id, :tour_id, 'Profile page', 'The basic functions and areas of the profile page are presented in this tour.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '5.0', '', 'root@localhost', 1406722657, 1631617118)";
        $settings = "(:tour_id, 1, 'standard', NULL, NULL)";
        $steps = "(:tour_id, 1, 'Profile tour', 'This tour provides a general overview of the profile page\'s structure.\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.', 'T', 0, '', 'dispatch.php/profile', '', '', '', 1406722657, 0),
        (:tour_id, 2, 'Personal picture', 'If you uploaded a picture, it will be displayed here. You can change it at all times.', 'RT', 0, '.avatar-normal', 'dispatch.php/profile', '', '', '', 1406722657, 0),
        (:tour_id, 3, 'Stud.IP-Score', 'The Stud.IP-Score increases with the activities in Stud.IP and thus represents the experience with  Stud.IP.', 'BL', 0, '#content TABLE:eq(0) TBODY:eq(0) TR:eq(0) TD:eq(0) A:eq(0)', 'dispatch.php/profile', '', '', '', 1406722657, 0),
        (:tour_id, 4, 'Announcements', 'You can publish personal announcements on this site.', 'B', 0, '#content ARTICLE:eq(0)  HEADER:eq(0)  H1:eq(0)', 'dispatch.php/profile', '', '', '', 1406722657, 0),
        (:tour_id, 5, 'New announcement', 'Click on the plus sign, if you would like to create an announcement.', 'BR', 0, '#content ARTICLE:eq(0)  HEADER:eq(0)  NAV:eq(0)  A:eq(0)', 'dispatch.php/profile', '', '', '', 1406722657, 0),
        (:tour_id, 6, 'Personal details', 'Your picture and additional user data can be changed on these sites.', 'B', 0, '#tabs li:eq(2)', 'dispatch.php/profile', '', '', 'dozent@studip.de', 1406722657, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = '4d41c9760a3248313236af202275107b';
        $tour = "('', :tour_id, 'Schreiben im Wiki', 'Die Tour erklärt, wie das Wiki bearbeitet werden kann.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'de', '5.0', '', 'root@localhost', 1441276241, 1631619212)";
        $settings = "(:tour_id, 1, 'standard', NULL, 1631619212)";
        $steps = "(:tour_id, 1, 'Schreiben im Wiki', 'Diese Tour gibt einen Überblick über die Erstellung und Bearbeitung von Wiki-Seiten.\r\n\r\nUm zum nächsten Schritt zu gelangen, klicken Sie bitte rechts unten auf \"Weiter\".', 'T', 0, '', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 2, 'Wiki-Startseite', 'Zeigt die Basis-Seite des Wikis an. Sie bildet die strukturelle Grundlage des gesamten Wikis.', 'R', 0, '#nav_wiki_start', 'wiki.php', '', '', 'dozent@studip.de', 1441276241, 0),
        (:tour_id, 3, 'Neue Seiten', 'Zeigt eine tabellarische Übersicht neu erstellter und neu bearbeiteter Wiki-Seiten an.', 'R', 0, '#nav_wiki_listnew', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 4, 'Alle Seiten', 'Zeigt eine tabellarische Übersicht aller Wiki-Seiten an.', 'R', 0, '#nav_wiki_listall', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 5, 'Wiki-Seite bearbeiten', 'Durch einen Klick auf die Schaltfläche \"Bearbeiten\" öffnet sich ein Editor, über den eine Wiki-Seite mit Inhalt gefüllt werden kann.\r\n\r\nDie Eingabe eines Namens in doppelten eckigen Klammern erzeugt eine neue Wiki-Seite und vernetzt sie mit der angezeigten Seite.', 'B', 0, '#main_content TABLE:eq(1)  TBODY:eq(0)  TR:eq(0)  TD:eq(0)  DIV:eq(0)  A:eq(0)', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 6, 'Inhalt einer Wiki-Seite löschen', 'Der Inhalt einer Wiki-Seite lässt sich mit Hilfe eines Klicks auf die Schaltfläche \"Löschen\" entfernen. Die Wiki-Seite bleibt dabei erhalten.', 'B', 0, '#main_content TABLE:eq(1)  TBODY:eq(0)  TR:eq(0)  TD:eq(0)  DIV:eq(0)  A:eq(1)', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 7, 'QuickLinks', 'Dieser Bildschirmbereich zeigt eine Liste von QuickLinks (Verweisen) auf Wiki-Seiten. Ein Klick auf einen QuickLink öffnet die korrelierende Wiki-Seite. Deren Inhalt lässt sich mit Hilfe der Schaltflächen \"Bearbeiten\" und \"Löschen\" gestalten.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(6)  DIV:eq(0)', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 8, 'QuickLinks bearbeiten', 'Über das Icon zum Bearbeiten von QuickLinks öffnet sich ein Editor.\r\n\r\nNeue QuickLinks lassen sich mit doppelten eckigen Klammern erstellen: [[Name]]. Das Löschen eines QuickLinks entfernt die korrelierende Seite aus der Liste.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(6)  DIV:eq(0)', 'wiki.php', '', '', 'root@localhost', 1441276241, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = '5d41c9760a3248313236af202275107b';
        $tour = "('', :tour_id, 'Editing the Wiki', 'This tour provides help for editing Wiki pages.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '5.0', '', 'root@localhost', 1441276241, 1631619212)";
        $settings = "(:tour_id, 1, 'standard', NULL, 1631619212)";
        $steps = "(:tour_id, 1, 'Editing the Wiki', 'This tour provides a general overview of how to create and edit Wiki pages.\r\n\r\nTo proceed, please click \"Continue\" on the lower-right button.', 'T', 0, '', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 2, 'WikiWikiWeb', 'Displays the basic Wiki page, which is the foundation of all further Wiki pages.', 'R', 0, '#nav_wiki_start', 'wiki.php', '', '', 'dozent@studip.de', 1441276241, 0),
        (:tour_id, 3, 'New pages', 'Displays a survey of all recently created or edited Wiki pages in table form.', 'R', 0, '#nav_wiki_listnew', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 4, 'All pages', 'Displays a survey of all Wiki pages in table form.', 'R', 0, '#nav_wiki_listall', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 5, 'Editing the a Wiki page', 'Clicking here will open an editor, allowing to fill a Wiki page with content.', 'B', 0, '#main_content TABLE:eq(1)  TBODY:eq(0)  TR:eq(0)  TD:eq(0)  DIV:eq(0)  A:eq(0)', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 6, 'Deleting the content of a Wiki page', 'Clicking here will delete all content and links of a Wiki page leaving it blank.', 'B', 0, '#main_content TABLE:eq(1)  TBODY:eq(0)  TR:eq(0)  TD:eq(0)  DIV:eq(0)  A:eq(1)', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 7, 'QuickLinks', 'This box displays links, leading to other Wiki pages. Selecting a link will forward to the related page.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(6)  DIV:eq(0)', 'wiki.php', '', '', '', 1441276241, 0),
        (:tour_id, 8, 'Editing QuickLinks', 'A click on this icon will open an editor to edit the QuickLinks.\r\n\r\nEntering a name within double square brackets like [[name]] in the editor will create a new QuickLink leading to a correlating page. Deleting a QuickLink will cause its deletion in the QuickLink box.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(6)  DIV:eq(0)', 'wiki.php', '', '', 'root@localhost', 1441276241, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = '19ac063e8319310d059d28379139b1cf';
        $tour = "(:tour_id, :tour_id, 'Studiengruppe anlegen', 'In dieser Tour wird das Anlegen von Studiengruppen erklärt.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'de', '5.0', '', '', 1405684299, 0)";
        $settings = "(:tour_id, 1, 'standard', NULL, NULL)";
        $steps = "(:tour_id, 1, 'Studiengruppe anlegen', 'Studiengruppen ermöglichen die Zusammenarbeit mit KommilitonInnen oder KolegInnen. Diese Tour gibt Ihnen einen Überblick darüber wie Sie Studiengruppen anlegen können.\r\n\r\nUm zum nächsten Schritt zu gelangen, klicken Sie bitte rechts unten auf \"Weiter\".', 'R', 0, '', 'dispatch.php/my_studygroups', '', '', '', 1405684423, 0),
        (:tour_id, 2, 'Studiengruppe anlegen', 'Mit Klick auf \"Neue Studiengruppe anlegen\" kann eine neue Studiengruppe angelegt werden.', 'BL', 0, '.sidebar-widget:eq(1) A:eq(0)', 'dispatch.php/my_studygroups', '', '.sidebar-widget:eq(1) li:eq(0) a:eq(0)', 'dozent@studip.de', 1406017730, 0),
        (:tour_id, 3, 'Studiengruppe benennen', 'Der Name einer Studiengruppe sollte aussagekräftig sein und einmalig im gesamten Stud.IP.', 'R', 0, '#wizard-name', 'dispatch.php/my_studygroups', '', '', '', 1405684720, 0),
        (:tour_id, 4, 'Beschreibung hinzufügen', 'Die Beschreibung ermöglicht es weitere Informationen anzuzeigen und somit das Auffinden der Gruppe zu erleichtern.', 'L', 0, '#wizard-description', 'dispatch.php/my_studygroups', '', '', 'dozent@studip.de', 1405684806, 0),
        (:tour_id, 5, 'Inhaltselemente zuordnen', 'Hier können Inhaltselemente aktiviert werden, welche  innerhalb der Studiengruppe zur Verfügung stehen sollen. Das Fragezeichen gibt nähere Informationen zur Bedeutung der einzelnen Inhaltselemente.', 'R', 0, '#wizard-access', 'dispatch.php/my_studygroups', '', '', 'dozent@studip.de', 1405685093, 0),
        (:tour_id, 6, 'Zugang festlegen', 'Mit diesem Drop-down-Menü kann der Zugang zur Studiengruppe eingeschränkt werden.\r\n\r\nBeim Zugang \"offen für alle\" können sich alle Studierenden frei eintragen und an der Gruppe beteiligen.\r\n\r\nBeim Zugang \"Auf Anfrage\" müssen Teilnehmer durch den Gruppengründer hinzugefügt werden.', 'R', 0, '#wizard-access', 'dispatch.php/my_studygroups', '', '', 'root@localhost', 1405685334, 0),
        (:tour_id, 7, 'Nutzungsbedingungen akzeptieren', 'Bei der Erstellung einer Studiengruppe müssen die Nutzungsbedingungen akzeptiert werden.', 'R', 0, '#ui-id-1 FORM:eq(0)  FIELDSET:eq(0)  LABEL:eq(4)', 'dispatch.php/my_studygroups', '', '', 'root@localhost', 1405685652, 0),
        (:tour_id, 8, 'Studiengruppe speichern', 'Nach dem Speichern einer Studiengruppe erscheint diese unter \"Veranstaltungen > Meine Studiengruppen\".', 'L', 0, '#content FORM TABLE TBODY TR TD :eq(14)', 'dispatch.php/my_studygroups', '', 'BUTTON.cancel:eq(0)', 'root@localhost', 1405686068, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = '9e9dca9b1214294b9605824bfe90fba1';
        $tour = "(:tour_id, :tour_id, 'Create study group', 'In this tour the creation of study groups is explained', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '5.0', '', '', 1405684299, 0)";
        $settings = "(:tour_id, 1, 'standard', NULL, NULL)";
        $steps = "(:tour_id, 1, 'Create study group', 'This tour provides an overview of the creation of study groups to cooperate with fellow students.\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.', 'R', 0, '', 'dispatch.php/my_studygroups', '', '', '', 1405684423, 0),
        (:tour_id, 2, 'Create study group', 'A new study group can be created with a click on \"create new study group\".', 'BL', 0, '.sidebar-widget:eq(1) A:eq(0)', 'dispatch.php/my_studygroups', '', '.sidebar-widget:eq(1) li:eq(0) a:eq(0)', 'dozent@studip.de', 1406017730, 0),
        (:tour_id, 3, 'Name a study group', 'The name of a study group should be meaningful and unique in the whole Stud.IP.', 'R', 0, '#wizard-name', 'dispatch.php/my_studygroups', '', '', '', 1405684720, 0),
        (:tour_id, 4, 'Add description', 'The description makes it possible to display additional information that makes it easier to find the group.', 'L', 0, '#wizard-description', 'dispatch.php/my_studygroups', '', '', 'dozent@studip.de', 1405684806, 0),
        (:tour_id, 5, 'Allocate content elements', 'Content elements can be activated here, which are to be available within the study group. The question mark provides more detailed information on the meaning of the individual content elements', 'R', 0, '#wizard-access', 'dispatch.php/my_studygroups', '', '', 'dozent@studip.de', 1405685093, 0),
        (:tour_id, 6, 'Stipulate access', 'The access to the study group can be restricted with this drop down menu.\n\nAll students can register freely and participate in the group with the access \"open for everyone\".\n\nWith the access \"upon request\" participants must be added by the group founder.', 'R', 0, '#wizard-access', 'dispatch.php/my_studygroups', '', '', 'root@localhost', 1405685334, 0),
        (:tour_id, 7, 'Accept terms of use', 'The terms of use have to be accepted before you can create a study group.', 'R', 0, '#ui-id-1 FORM:eq(0)  FIELDSET:eq(0)  LABEL:eq(4)', 'dispatch.php/my_studygroups', '', '', 'root@localhost', 1405685652, 0),
        (:tour_id, 8, 'Save study group', 'After you saved a study group it will appear under \"My courses\" > \"My study groups\".', 'L', 0, '#content FORM TABLE TBODY TR TD :eq(14)', 'dispatch.php/my_studygroups', '', 'BUTTON.cancel:eq(0)', 'root@localhost', 1405686068, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = '6849293baa05be5bef8ff438dc7c438b';
        $tour = "(:tour_id, :tour_id, 'Suche', 'In dieser Feature-Tour werden die wichtigsten Funktionen der Suche vorgestellt.', 'tour', 'autor,tutor,dozent,admin,root', 1, 'de', '5.0', '', '', 1405519609, 0)";
        $settings = "(:tour_id, 1, 'standard', NULL, NULL)";
        $steps = "(:tour_id, 1, 'Suche', 'Diese Tour gibt Ihnen einen Überblick über die wichtigsten Funktionen der \"Suche\".\r\n\r\nUm auf den nächsten Schritt zu kommen, klicken Sie bitte rechts unten auf \"Weiter\".', 'B', 0, '', 'dispatch.php/search/globalsearch', '', '', 'root@localhost', 1405519865, 0),
        (:tour_id, 2, 'Suchbegriff eingeben', 'In dieses Eingabefeld kann ein Suchbegriff (wie z.B. der Veranstaltungsname, Lehrperson) eingegeben werden.', 'B', 0, '#search-input', 'dispatch.php/search/globalsearch', '', '', 'root@localhost', 1405520106, 0),
        (:tour_id, 3, 'Semesterauswahl', 'Durch einen Klick auf das Drop-Down Menü kann bestimmt werden, auf welches Semester sich der Suchbegriff beziehen soll. \r\n\r\nStandardgemäß ist das aktuelle Semester eingestellt.', 'TL', 0, '#semester_filter DIV:eq(0)', 'dispatch.php/search/globalsearch', '', '', 'root@localhost', 1405520208, 0),
        (:tour_id, 4, 'Navigation', 'Falls nur in einem bestimmten Bereich (wie z.B. Lehre) gesucht werden soll, kann dieser hier ausgewählt werden.', 'BL', 0, '#tabs', 'dispatch.php/search/globalsearch', '', '', 'dozent@studip.de', 1406121826, 0),
        (:tour_id, 5, 'Schnellsuche', 'Die Schnellsuche ist auch auf anderen Seiten von Stud.IP jederzeit verfügbar. Nach der Eingabe eines Stichwortes, wird mit \"Enter\" bestätigt, oder auf die Lupe rechts neben dem Feld geklickt.', 'B', 0, '#globalsearch-input', 'dispatch.php/search/globalsearch', '', '', 'root@localhost', 1405520634, 0),
        (:tour_id, 6, 'Weitere Suchmöglichkeiten', 'Neben Veranstaltungen besteht auch die Möglichkeit, im Archiv, nach Personen, nach Einrichtungen oder nach Ressourcen zu suchen.', 'R', 0, '#nav_search_resources A SPAN', 'dispatch.php/search/globalsearch', '', '', 'root@localhost', 1405520751, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = '7af1e1fb7f53c910ba9f42f43a71c723';
        $tour = "(:tour_id, :tour_id, 'Search', 'In this feature tour the most important search functions are explained', 'tour', 'autor,tutor,dozent,admin,root', 1, 'en', '5.0', '', '', 1405519609, 0)";
        $settings = "(:tour_id, 1, 'standard', NULL, NULL)";
        $steps = "(:tour_id, 1, 'Search', 'This tour provides an overview of the supplied search options.\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.', 'B', 0, '', 'dispatch.php/search/globalsearch', '', '', 'root@localhost', 1405519865, 0),
        (:tour_id, 2, 'Enter search term', 'A search term (such as event name, lecturer) can be entered in this input field.', 'B', 0, '#search-input', 'dispatch.php/search/globalsearch', '', '', 'root@localhost', 1405520106, 0),
        (:tour_id, 3, 'Semester selection', 'With a click on the drop-down menu you can choose to which semester the search term should refer. \n\nThe current semester is set as standard.', 'TL', 0, '#semester_filter DIV:eq(0)', 'dispatch.php/search/globalsearch', '', '', 'root@localhost', 1405520208, 0),
        (:tour_id, 4, 'Navigation', 'If you want to search only one particular area, you can select one here.', 'BL', 0, '#tabs', 'dispatch.php/search/globalsearch', '', '', 'dozent@studip.de', 1406121826, 0),
        (:tour_id, 5, 'Quick search', 'The quick search is also available on other sites of Stud.IP at all times. After entering a key word it is confirmed with \"Enter\" or by clicking the magnifying glass on the right next to the field.', 'B', 0, '#globalsearch-input', 'dispatch.php/search/globalsearch', '', '', 'root@localhost', 1405520634, 0),
        (:tour_id, 6, 'Further search possibilities', 'In addition to searching for events there is also the possibility to search the archive for persons, facilities, or resources.', 'R', 0, '#nav_search_resources A SPAN', 'dispatch.php/search/globalsearch', '', '', 'root@localhost', 1405520751, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = 'edfcf78c614869724f93488c4ed09582';
        $tour = "(:tour_id, :tour_id, 'Teilnehmerverwaltung', 'In dieser Tour werden die Verwaltungsoptionen der Teilnehmerverwaltung erklärt.', 'tour', 'tutor,dozent,admin,root', 1, 'de', '5.0', '', '', 1405688156, 0)";
        $settings = "(:tour_id, 1, 'standard', NULL, NULL)";
        $steps = "(:tour_id, 1, 'Teilnehmerverwaltung', 'Diese Tour gibt einen Überblick über die Teilnehmerverwaltung einer Veranstaltung.\r\n\r\nUm zum nächsten Schritt zu gelangen, klicken Sie bitte rechts unten auf \"Weiter\".', 'B', 0, '', 'dispatch.php/course/members', '', '', '', 1405688399, 0),
        (:tour_id, 2, 'Personen eintragen', 'Mit diesen Funktionen können entweder einzelne Personen in Stud.IP gesucht und direkt mit dem Status Dozent, Tutor oder Autor eintragen werden. Es ist auch möglich eine Teilnehmerliste einzugeben, um viele Personen auf einmal als TutorIn der Veranstaltung zuzuordnen.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(6)  DIV:eq(0)', 'dispatch.php/course/members', '', '', '', 1405688707, 0),
        (:tour_id, 3, 'Hochstufen / Herabstufen', 'Um eine bereits eingetragene Person zum/zur TutorIn hochzustufen oder zum/zur LeserIn herabzustufen, wählen Sie diese Person in der Liste aus und führen Sie mit Hilfe des Dropdown-Menü die gewünschte Aktion aus.', 'T', 0, '#autor CAPTION', 'dispatch.php/course/members', '', '', '', 1405690324, 0),
        (:tour_id, 4, 'Rundmail verschicken', 'Hier kann eine Rundmail an alle Teilnehmende der Veranstaltung verschickt werden.', 'R', 0, '#link-71a939b1cddd28322f902cdfbc330250 A:eq(0)', 'dispatch.php/course/members', '', '', 'dozent@studip.de', 1406636964, 0),
        (:tour_id, 5, 'Rundmail an Nutzergruppe versenden', 'Weiterhin besteht die Möglichkeit eine Rundmail an einzelne Nutzergruppen zu versenden.', 'BR', 0, '#autor CAPTION:eq(0)  SPAN:eq(0)  A:eq(0)  IMG:eq(0)', 'dispatch.php/course/members', '', '', '', 1406637123, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);

        $tour_id = 'c89ce8e097f212e75686f73cc5008711';
        $tour = "(:tour_id, :tour_id, 'Participant administration', 'The administration options of the participant administration are explained in this tour.', 'tour', 'tutor,dozent,admin,root', 1, 'en', '5.0', '', '', 1405688156, 0)";
        $settings = "(:tour_id, 1, 'standard', NULL, NULL)";
        $steps = "(:tour_id, 1, 'Participant administration', 'This tour provides an overview of the participant administration\'s options.\r\n\rTo proceed, please click \"Continue\" in the lower-right corner.', 'B', 0, '', 'dispatch.php/course/members', '', '', '', 1405688399, 0),
        (:tour_id, 2, 'Add persons', 'With these functions you can search for individual persons in Stud.IP and directly  select them as lecturer, tutor or author. It is also possible to insert a list of participants in order to allocate several persons as a tutor of the event at the same time.', 'R', 0, '#layout-sidebar SECTION:eq(0)  DIV:eq(6)  DIV:eq(0)', 'dispatch.php/course/members', '', '', '', 1405688707, 0),
        (:tour_id, 3, 'Upgrade/ downgrade', 'In order to upgrade an already enroled person to a tutor, or to downgrade them to a reader select this person in the list and carry out the requested action by using the dropdown menu.', 'T', 0, '#autor CAPTION', 'dispatch.php/course/members', '', '', '', 1405690324, 0),
        (:tour_id, 4, 'Send circular e-mail', 'A circular e-mail can be sent to all participants of the event here.', 'R', 0, '#link-71a939b1cddd28322f902cdfbc330250 A:eq(0)', 'dispatch.php/course/members', '', '', 'dozent@studip.de', 1406636964, 0),
        (:tour_id, 5, 'Send circular e-mail to user group', 'There is further the possibility to send a circular e-mail to individual user groups.', 'BR', 0, '#autor CAPTION:eq(0)  SPAN:eq(0)  A:eq(0)  IMG:eq(0)', 'dispatch.php/course/members', '', '', '', 1406637123, 0)";
        $this->updateTour($tour_id, $tour, $settings, $steps);
    }


    public function down()
    {

    }

}
