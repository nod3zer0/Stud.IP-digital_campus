<?php
class AddAccessibilityDisclaimer extends Migration
{
    public function description()
    {
        return 'Adds the configuration ACCESSIBILITY_DISCLAIMER_URL, and sample page for disclaimer.';
    }

    protected function up()
    {
        $db = DBManager::get();

        $text = '[lang=de]<!--HTML-->
<h1>Mustertext Erklärung zur Barrierefreiheit für Stud.IP</h1>

<p>[Text in eckigen Klammern ist ggf. zu ergänzen, zu streichen oder sprachlich anzupassen, je nachdem, wie das Ergebnis der Überprüfung der Barrierefreiheit ausfällt.]</p>

<p>Diese Erklärung zur Barrierefreiheit gilt für die Stud.IP-Installation unter der [URL der ergänzen; bitte Version und Datum angeben] der [Betreiber der Stud.IP-Installation ergänzen].</p>

<p>Als öffentliche Stelle im Sinne der Richtlinie (EU) 2016/2102 sind wir bemüht, unsere Websites und mobilen Anwendungen im Einklang mit den Bestimmungen des Behindertengleichstellungsgesetzes des Bundes (BGG) sowie der Barrierefreien-Informationstechnik-Verordnung (BITV 2.0) zur Umsetzung der Richtlinie (EU) 2016/2102 barrierefrei zugänglich zu machen.</p>

<p>[Hier ggf. jeweilige Landesverordnung zusätzlich einfügen, z.B. für Niedersachsen <em>§ 9 NBGG.</em>]</p>

<h2>Stand der Vereinbarkeit mit den Anforderungen</h2>

<p>Die Anforderungen der Barrierefreiheit ergeben sich aus §§ 3 Absätze 1 bis 4 und 4 der BITV 2.0, die auf der Grundlage von § 12d BGG erlassen wurde.</p>

<p>Die Überprüfung der Einhaltung der Anforderungen beruht auf</p>

<p>einer von Materna Information &amp; Communications SE im Zeitraum von Ende Januar bis Anfang Februar 2021 vorgenommenen Bewertung. Maßstab der Prüfung ist die EN 301 549 und der A sowie AA Status der WCAG 2.1. Überprüft wurden die Vorgaben der WCAG 2.1 (Konformitätsstufen A und AA) anhand der 60 Prüfschritte des BITV/WCAG-Tests.</p>

<p>Die Überprüfung bezieht sich auf das Stud.IP-Release 4.6. [Plugins und Inhalte müssen standortspezifisch geprüft und ggf. dokumentiert/diese Erklärung angepasst werden. Bitte ggf. ergänzen.]</p>

<p>Diese Stud.IP-Installation ist nicht vollständig mit den für uns geltenden Vorschriften zur Barrierefreiheit vereinbar. Im Einzelnen:</p>

<ul>
	<li>
	<p>Überschriftenhierarchien werden auf manchen Seiten nicht vollständig eingehalten.</p>
	</li>
	<li>
	<p>Für Bilder, Bedienelemente und grafische Elemente sind in manchen Fällen keine, falsche oder unzureichende Alternativen vorhanden.</p>
	</li>
	<li>
	<p>Grafiken und Bedienelementen fehlen in manchen Fällen korrekte Auszeichnungen, so dass sie von Assistenzsystemen nicht richtig erfasst werden können.</p>
	</li>
	<li>
	<p>Die Sprache in Alternativtexten ist teilweise in Englisch angegeben ohne das der Sprachwechsel korrekt ausgezeichnet ist.</p>
	</li>
	<li>
	<p>Listeneinträge, Tabellen(-spalten) und Formulare sind teilweise nicht korrekt ausgezeichnet.</p>
	</li>
	<li>
	<p>Die sichtbare Reihenfolge von Seitenelementen weicht teilweise von der Reihenfolge im Quelltext ab.</p>
	</li>
	<li>
	<p>Die Mindestanforderung an Kontraste ist nicht überall erfüllt.</p>
	</li>
	<li>
	<p>Die Tastatursteuerung ist nicht uneingeschränkt benutzbar.</p>
	</li>
	<li>
	<p>Auf der Startseite fehlt die Bereitstellung der Erläuterungen über die Website in Leichter Sprache und in Deutscher Gebärdensprache.</p>
	</li>
</ul>

<p>Zudem können von Nutzerinnen und Nutzern eingestellte Inhalte, z.B. PDFs oder Videos, Barrieren aufweisen.</p>

<p>Stud.IP wird derzeit in Bezug auf Barrierefreiheit überarbeitet. Folgende Maßnahmen zur Verringerung von Barrieren werden voraussichtlich in das Release von Stud.IP 5.1 und 5.2 einfließen:</p>

<ul>
	<li>
	<p>Attribute und Alternativtexte werden hinzugefügt und korrigiert, damit Screen Reader Schaltflächen, Links, Bedienelemente und Grafiken korrekt interpretieren und passende Texte ausgeben können.</p>
	</li>
	<li>
	<p>Die Hierarchien von Überschriften und Reihenfolge von Seitenelementen werden überarbeitet/korrigiert.</p>
	</li>
	<li>
	<p>Sprachauszeichnungen werden vereinheitlicht.</p>
	</li>
	<li>
	<p>Listeneinträge, Tabellen(-spalten) und Formulare werden korrekt ausgezeichnet und sinnvoll verknüpft.</p>
	</li>
	<li>
	<p>Eine Möglichkeit den Kontrast zu verändern, wird implementiert.</p>
	</li>
	<li>
	<p>Die Tastatursteuerung korrigiert.</p>
	</li>
	<li>
	<p>Aktionsmenüs und Akkordeonelemente werden überarbeitet.</p>
	</li>
	<li>
	<p>Die responsive Navigation wird verbessert.</p>
	</li>
</ul>

<p>Folgende Inhalte sind aufgrund der Absicht, ein höheres Maß an digitaler Barrierefreiheit als gesetzlich gefordert umzusetzen, realisiert:<br />
[Geben Sie die jeweiligen Inhalte an]</p>

<h2>Datum der Erstellung bzw. der letzten Aktualisierung der Erklärung</h2>

<p>Diese Erklärung wurde am [09/2021] erstellt und zuletzt am [Datum] aktualisiert.</p>

<h2>Barrieren melden: Kontakt zu den Feedback Ansprechpartnern</h2>

<p>Sie möchten uns bestehende Barrieren mitteilen oder Informationen zur Umsetzung der Barrierefreiheit erfragen? Für Ihr Feedback sowie alle weiteren Informationen sprechen Sie unsere verantwortlichen Kontaktpersonen unter xxx an.</p>

<p>[verlinkte URL mit Namen des Feedback-Mechanismus, z. B. „Barrieren melden“ angeben. Dabei sollte der Leitfaden „Erklärung zur Barrierefreiheit“ und der Leitfaden „Feedback-Mechanismus“ beachtet werden]</p>

<h2>Schlichtungsverfahren</h2>

<p>[Nicht zutreffende Stelle streichen ggf. Stelle Ihres Bundeslandes einfügen]</p>

<p>Wenn auch nach Ihrem Feedback an den oben genannten Kontakt keine zufriedenstellende Lösung gefunden wurde, können Sie sich an die Schlichtungsstelle nach § 16 BGG wenden. Die Schlichtungsstelle BGG hat die Aufgabe, bei Konflikten zum Thema Barrierefreiheit zwischen Menschen mit Behinderungen und öffentlichen Stellen des Bundes eine außergerichtliche Streitbeilegung zu unterstützen. Das Schlichtungsverfahren ist kostenlos. Es muss kein Rechtsbeistand eingeschaltet werden. Weitere Informationen zum Schlichtungsverfahren und den Möglichkeiten der Antragstellung erhalten Sie unter: <u><a href="http://www.schlichtungsstelle-bgg.de/">www.schlichtungsstelle-bgg.de</a></u>.</p>

<p>Direkt kontaktieren können Sie die Schlichtungsstelle BGG unter <u><a href="mailto:info@schlichtungsstelle-bgg.de">info@schlichtungsstelle-bgg.de</a></u>.</p>
(:reportbarrierlink:)[/lang]

[lang=en]
<!--HTML-->
<h1>Sample Text Accessibility Statement</h1>

<p>[Text in square brackets may need to be added, deleted, or linguistically adjusted depending on the outcome of the accessibility review].</p>

<p>This accessibility statement applies to the Stud.IP installation at the [add URL of; please specify version and date] of the [add operator of Stud.IP installation].</p>

<p>As a public body within the meaning of Directive (EU) 2016/2102, we strive to make our websites and mobile applications accessible in accordance with the provisions of the Federal Disability Equality Act (BGG) and the Barrier-Free Information Technology Ordinance (BITV 2.0) implementing Directive (EU) 2016/2102.</p>

<p>[Here, if necessary, insert the respective state ordinance additionally, e.g. for Lower Saxony § 9 NBGG].</p>

<h2>Status of compatibility with the requirements</h2>

<p>The accessibility requirements result from §§ 3 paragraphs 1 to 4 and 4 of BITV 2.0, which was issued on the basis of § 12d BGG.</p>

<p>The review of compliance with the requirements is based on</p>

<p>an assessment performed by Materna Information &amp; Communications SE in the period from the end of January to the beginning of February 2021. The benchmark for the test is EN 301 549 and the A and AA status of WCAG 2.1. The requirements of WCAG 2.1 (conformance levels A and AA) were checked using the 60 test steps of the BITV/WCAG test.</p>

<p>The check refers to Stud.IP release 4.6. [Plugins and content must be checked site-specifically and documented/adapted to this statement if necessary. Please add if necessary].</p>

<p>This Stud.IP installation is not fully compliant with the accessibility regulations that apply to us. In detail:</p>

<ul>
	<li>
	<p>Heading hierarchies are not fully respected on some pages.</p>
	</li>
	<li>
	<p>In some cases, there are no, incorrect or insufficient alternatives for images, control elements and graphical elements.</p>
	</li>
	<li>
	<p>Graphics and control elements lack correct markup in some cases, so that they cannot be correctly detected by assistance systems.</p>
	</li>
	<li>
	<p>The language in alternative texts is sometimes specified in English without the language change being correctly marked.</p>
	</li>
	<li>
	<p>List entries, tables (columns) and forms are sometimes not correctly labeled.</p>
	</li>
	<li>
	<p>The visible order of page elements sometimes differs from the order in the source text.</p>
	</li>
	<li>
	<p>The minimum requirement for contrasts is not met everywhere.</p>
	</li>
	<li>
	<p>The keyboard control is not fully usable.</p>
	</li>
	<li>
	<p>The home page lacks the provision of explanations about the website in plain language and in German sign language.</p>
	</li>
</ul>

<p>In addition, content posted by users, e.g. PDFs or videos, may have barriers.<br />
&nbsp;</p>

<p>Stud.IP is currently being revised with regard to accessibility. The following measures to reduce barriers are expected to be included in the release of Stud.IP 5.1 and 5.2:</p>

<ul>
	<li>
	<p>Attributes and alternative texts are added and corrected so that screen readers can correctly interpret buttons, links, controls and graphics and output appropriate texts.</p>
	</li>
	<li>
	<p>Heading hierarchies and page element order are revised/corrected.</p>
	</li>
	<li>
	<p>Language mark-ups will be standardized.</p>
	</li>
	<li>
	<p>List entries, tables (columns) and forms will be labelled correctly and linked in a meaningful way.</p>
	</li>
	<li>
	<p>A possibility to change the contrast is implemented.</p>
	</li>
	<li>
	<p>The keyboard control is corrected.</p>
	</li>
	<li>
	<p>Action menus and accordion elements are revised.</p>
	</li>
	<li>
	<p>Responsive navigation will be improved.</p>
	</li>
	<li>
	<p>The following content is implemented due to the intention to implement a higher level of digital accessibility than required by law:</p>
	</li>
</ul>

<p>[Specify the respective content]</p>

<h2>Date of preparation or last update of the declaration</h2>

<p>This statement was created on [09/2021] and last updated on [date].</p>

<h2>Report Barriers: Contact Feedback Contacts</h2>

<p>Would you like to report existing barriers or request information on implementing accessibility? For your feedback as well as any further information, please contact our responsible contact persons at xxx.</p>

<p>[provide linked URL with name of feedback mechanism, e.g. "Report barriers". The "Accessibility Statement" guide and the "Feedback Mechanism" guide should be followed].</p>

<h2>Arbitration</h2>

<p>[Delete non-applicable body, if necessary insert body of your federal state].</p>

<p>If a satisfactory solution has not been found even after you have sent feedback to the above-mentioned contact, you can turn to the conciliation body pursuant to Section 16 BGG. The BGG conciliation body is tasked with supporting out-of-court dispute resolution in the event of conflicts on the topic of accessibility between people with disabilities and federal public agencies. The conciliation procedure is free of charge. No legal counsel needs to be involved. For more information on the conciliation process and how to submit a request, visit: www.schlichtungsstelle-bgg.de.</p>

<p>You can contact the BGG conciliation body directly at <u><a href="mailto:info@schlichtungsstelle-bgg.de">info@schlichtungsstelle-bgg.de</a></u>.</p>
(:reportbarrierlink:)[/lang]';

        $query = "SELECT `rubric_id`, `detail_id`
                  FROM `siteinfo_details`
                  WHERE `name` = 'Barrierefreiheitserklärung'";
        $result = $db->fetchOne($query);
        if (!$result) {
            $db->execute("INSERT INTO `siteinfo_details` (`detail_id`, `rubric_id`, `position`, `name`, `content`)
            VALUES (NULL, '1', NULL, 'Barrierefreiheitserklärung', ?)", [$text]);
            $result = $db->fetchOne($query);
        } else {
            //disable Plugin and prevent down migration
            $db->exec("UPDATE `plugins` SET `enabled` = 'no' WHERE `pluginclassname` = 'AccessibleForm'");
            $db->exec("DELETE FROM `schema_version` WHERE `domain` = 'Barrierefreies Formular'");
        }
        $barrierefreiheits_url = "dispatch.php/siteinfo/show/{$result['rubric_id']}/{$result['detail_id']}";
        $db->execute(
            "INSERT IGNORE INTO `config`
             (`field`, `type`, `range`, `value`, `section`, `description`, `mkdate`, `chdate`)
             VALUES
             (
                 'ACCESSIBILITY_DISCLAIMER_URL', 'string', 'global', '', 'accessibility', ?,
                 UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
             )", ['URL der Barrierefreiheitserklärung, die in der Fußleiste verlinkt wird. Wenn Sie den Mustertext im Impressum verwenden, tragen Sie diese URL ein: ' . $barrierefreiheits_url]
        );
    }

    protected function down()
    {
        $db = DBManager::get();

        $db->exec("DELETE FROM `config_values` WHERE `field` = 'ACCESSIBILITY_DISCLAIMER_URL'");
        $db->exec("DELETE FROM `config` WHERE `field` = 'ACCESSIBILITY_DISCLAIMER_URL'");
        $db->exec("DELETE FROM `siteinfo_details` WHERE name = 'Barrierefreiheitserklärung'");
    }
}
