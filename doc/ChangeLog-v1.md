# 20.01.2011 v 1.11.1

http://develop.studip.de/trac/query?milestone=Stud.IP+1.11.1

- Titel einer Veranstaltung erlaubt einschleusen von JS [#1399]
- Fehler beim auf- und zuklappen aller Termine im Ablaufplan [#1419]
- $wiki_directives kann über die URL überschrieben werden [#1654]
- Geshrunkene Forumsbeiträge lassen sich nicht nicht editieren/zitieren [#69]
- Doppeltes Posting nach Back-Klick [#81]
- zitieren/antworten auf geshrinkte Beiträge im Forum nicht möglich [#133]
- ZIP-File upload berücksichtigt verbotene Dateitypen nicht (BIEST00124) [#210]
- Download von Ordner als ZIP fehlt bei Status "user" in der Veranstaltung (BIEST00146) [#218]
- Aufklappen von Suchergebnissen in der Ressourcensuche [#248]
- kein "vCard herunterladen" mit IE und https möglich [#290]
- Belegungen, die im Belegungsplan außerhalb der aktuellen Startuhrzeit liegen, werden falsch dargestellt. [#713]
- Nachrichten klappen zu [#925]
- Sichtbarkeit von Studiengruppen nicht änderbar [#926]
- News ändern / anlegen - ohne Bereich [#970]
- Anmeldebeschränkung "gesperrt" fehlt beim VA-Anlegen [#1027]
- Infobox-Bilder werden mit Assets::img() behandelt [#1036]
- Bearbeiten von Gruppen im Adressbuch nicht möglich [#1062]
- Dateiname mit Apostroph wird beim Hochladen abgeschnitten [#1072]
- Fehlende MessageBox bei Meine Lernmodule [#1090]
- Bilder für Veranstaltungen/Einrichtungen haben Tooltip "unbekannt" [#1113]
- "Sie können sich nicht als Leser eintragen lassen" ist wie Erfolgsmeldung formatiert [#1129]
- falsche Semesterausgabe bei templatebasierten Veranstaltungslisten [#1134]
- Schnellsuche wirft MySQL Fehler wenn "Alle Semester" gewählt sind [#1136]
- Download von Plugins scheitert mit IE und https [#1140]
- checkObject() checkt zu spät [#1142]
- Fatal error bei Links, die Login erfordern und cid enthalten [#1143]
- Text "Studienmodule" bei Auswahl der anzuzeigenden Studienbereiche [#1154]
- Dateibereich: Sortierung nach Größe vs Downloads [#1165]
- Fehler beim Aufrufen der Mitgliederseite einer Studiengruppe [#1169]
- Vorlagenauswahl bei Gruppen/Funktionen in Veranstaltungen kaputt [#1175]
- Dateibereich Datei-Aktualisieren-Dialog statt Dateiupload [#1183]
- Admin-Account als Studiengruppengründer [#1186]
- Studiengruppen-Zugang anderer Nutzerdomänen [#1188]
- Adminbereich Einrichtungshierarchie Objektsuche ohne htmlReady [#1190]
- Verschachtelung von TD und TR korrigieren [#1194]
- falsche Meldung beim Deaktivieren der Ordnerberechtigungen [#1195]
- Zeichen aus Windows-1252 werden falsch dargestellt [#1196]
- Fehlermeldung beim Umstellen des Auth-Plugins [#1201]
- unterschiedliche Schriftgrößen auf "Meine Veranstaltungen" [#1206]
- Dateidownload: Kann keine Datei > memorylimit herunter laden [#1212]
- Fehlende "down"-Teile in Migration 49 [#1213]
- Inline-style-Anweisung mit geschweiften Klammern wird ignoriert [#1217]
- index.php überschreibt _include_additional_header [#1219]
- Reihenfolge der Buttons beim Archivieren ist vertauscht [#1234]
- leere Spalten im CSV-Export der Teilnehmerliste [#1238]
- Freie Veranstaltungen mit Schreibzugriff [#1242]
- Mails, die per smtp verschickt werden, bekommen u.U. falsches Datum [#1246]
- TrailsController::url_for() umgeht den URLHelper [#1254]
- Ausrufungszeichen alleine auf einer Zeile erzeugt eine Überschrift [#1255]
- Tippfehler in Stud.IP [#1264]
- Tippfehler in Stud.IP [#1266]
- Umfrageerstellung in Studiengruppen nicht möglich [#1278]
- Buttons ohne Funktion in der Tagesansicht des Terminplans [#1279]
- Ressourcenverwaltung: mehrtägiger Termin kann nicht am gleichen Monatstag enden [#1284]
- root-Account wird mit Einrichtungszuordnung angelegt [#1295]
- Freie Raumangabe beim Erstellen eines Einzeltermins bleibt unbeachtet [#1306]
- Markierung "öffentliche Veranstaltung" nur für angemeldete Nutzer [#1311]
- "Einrichtungssbild" korrigieren [#1315]
- Link "neue Veranstaltung anlegen" fehlt auf der Startseite [#1318]
- Studiengruppen: Benutzer werden versehentlich eingeladen [#1327]
- Externe Seiten: Text "Angaben zum Element" ist verwirrend [#1331]
- Fehlerhaftes SQL in Druckansicht / Ablaufplan eines Seminars [#1358]
- Kennzeichnung notwendiger Felder auf "Literatureintrag bearbeiten" [#1365]
- Zwei Löschdialoge beim Löschen einer Literaturliste [#1366]
- XSS bei "Person zum Eintrag in das Adressbuch suchen:" [#1377]
- XSS in Veranstaltungsnummer [#1378]
- XSS in ECTS-Punkten [#1379]
- Studiengruppe bietet Aktivieren von Plugins an, für die der Nutzer keine Rechte hat [#1387]
- Aufruf von "Laufende Anmeldeverfahren" als Admin dauert sehr lange [#1390]
- $_GLOBALS gibt es nicht [#1393]
- Freie Informationsseite: Neue Seite speichern führt zu Fehler [#1413]
- Studiengruppe anlegen: Fehlermeldungen werden nicht angezeigt [#1414]
- Studiengruppen: Forumbeiträge verschieben funzt nicht [#1415]
- Mitarbeiterseite für Admins zeigt Fehler, wenn keine Einrichtung gewählt ist [#1420]
- Benutzerverwaltung: Anzeige "gesperrt von" zeigt letzten Bearbeiter des gesamten Datensatzes [#1431]
- Kaputte Textstrings auf externen Seiten [#1433]
- Fehler bei den Statusgruppen [#1434]
- Votings auf der Profileseite nicht bearbeitbar [#1448]
- XSS in der Druckansicht der Semesterbelegung [#1461]
- I18N Bug in Messaging-Einstellungen [#1462]
- I18N Bug in Einstellungen -> RSS-Feeds [#1463]
- Ablaufplan-Download funktioniert nicht im IE [#1464]
- Quickjump im Ablaufplan wird falsch dargestellt [#1467]
- neue Stud.IP Version: Export von Terminen [#1506]
- show_all_dates() verursacht zu viele DB-Anfragen [#1507]
- Seminar::addIssue() trägt neue Themen falsch ein [#1515]
- Externe Seiten Seminarbrowser behandelt Veranstaltungsbeschreibung mit Formatready [#1522]
- HTML der Seite Veranstaltungen > Druckansicht wird nicht korrekt beendet [#1545]
- Druckansicht Semesterbelegungsplan [#1549]
- HTML der Forumssuche wird nicht korrekt beendet [#1564]
- Fehlermeldung nach Klick auf News-Eintrag in freier Veranstaltung [#1567]
- HTML der Wiki-Seiten wird nicht korrekt beendet [#1568]
- HTML der Seite Zusatzangaben für Veranstaltungen wird nicht korrekt beendet [#1570]
- Veranstaltungssuche: Anzeige gruppiert nach Bereichen zieht viel Last [#1571]
- Veranstaltungssuche: Anzeige der Termine berücksichtigt nicht das Semester [#1572]
- Typo in Veranstaltung Kurzinfo (seminare_main.php) bei Meldung Zusatzinformationen [#1573]
- Nach dem Aktualisieren ist vor dem Aktualisieren (Dateibereich) [#1574]
- Überflüssiges alt-Attribut in Links in Veranstaltungssuche [#1575]
- Studiengruppen: Einladungen verschicken mit SQL Fehler [#1583]
- Studiengruppen: "navigation item '/admin/course/vote' not found" [#1598]
- Themenordner verlieren Titel beim Bearbeiten (aber nicht fertig bearbeiten) [#1602]
- freie.php braucht kein login_if [#1610]
- persönliche Seite: "navigation item '/admin/course/vote' not found" [#1615]
- fatal error beim Archivieren mit aktivierter Lernmodulschnittstelle [#1622]
- RolePersistence verträgt sich nicht mit cli [#1642]
- Performance: Suche nach Räumen mit gewünschter Belegungszeit [#1644]
- sem_verify.php zeigt seltsame Dinge bei Aufruf als nobody [#1647]
- SQL-Fehler beim Anlegen von Einrichtungen [#1649]
- Wortdoppelung auf Seite "Zugangsberechtigungen" [#1236]

# 20.04.2010 v 1.11

http://develop.studip.de/trac/query?milestone=Stud.IP+1.11

- Falsches Element der Subnavigation aktiv [#75]
- Verwaltung von "Zusatzangaben" [#167]
- Interfaces für Plugins [#345]
- Seitenkodierung auf WINDOWS-1252 ändern und korrektes UTF-8 Handling [#358]
- svn:keywords Property entfernen [#417]
- Plugin- und Rollenverwaltung in der Kern verschieben [#435]
- Sichtbarkeit von Veranstaltungen (Button erweitert) [#444]
- Externe Seiten: Avatare in Personenlisten [#463]
- Nachricht an mehrere Teilnehmer schicken [#525]
- Pagination auf user_online [#557]
- Sicherheitsabfragen beim Löschen anpassen (modaler Dialog) [#640]
- Anzeige akademischer Titel bei Veranstaltungssuche [#674]
- alte News / Newsarchiv als externe Seiten ausgeben [#723]
- StEP00177: Navigation 2.0 [#748]
- Sortierung unter Gruppen/Funktionen vereinheitlichen [#764]
- formatReady() erzeugt Leerfeld [#776]
- TIC: phplot erneuern [#779]
- Erweiterung des im Impressum verfügbaren Markups [#780]
- Plugins sollen HTML-Code in die Seite einbauen können [#781]
- Kronen für Nutzer mit den meisten Forumsbeiträgen etc. [#787]
- Bug im Menüsystem auf der score.php [#791]
- StEP00178: Event-System für Stud.IP [#793]
- Fatal Error (fehlende Navigationsklasse) wenn keine Verbindung zur Datenbank [#814]
- Suche auf "Titel,DozentIn,Nummer" voreinstellen [#815]
- Standardbetreff bei Mails an Teilnehmer einer Veranstaltung [#816]
- Gesamtzahl der Teilnehmer bei gruppierten Veranstaltungen [#833]
- Avatare in der Personensuche [#834]
- fehlende Größenangabe bei den Avataren [#835]
- Ausgabe von Homepage-Plugins auf externen Seiten [#841]
- Adminseite für Studiengruppen verbessern [#849]
- StEP00175: Umgestaltung Dateibereich [#851]
- HTML-Export für Ablaufplan [#853]
- Fatal Error: DB_Seminar [#858]
- Logging von SemTree Events [#860]
- Design Glitches verbessern [#869]
- Termine und Kalender [#877]
- Forensuche für "nobody" bei freien Veranstaltungen [#882]
- Fatal error: Cannot access protected property Trails_Exception::$line [#886]
- Raumanfragen mit Lehrenden sichtbar [#895]
- Non-static method called statically [#899]
- News aktualisieren - Datumproblem [#909]
- Links auf der Startseite bekommen "username" URL-Parameter [#910]
- Behandlung von fehlenden Cache-Klassen [#911]
- Markup-Toolbar für Wiki-Seiten [#915]
- StEP00181: Dateibasiertes caching als default [#920]
- Nachrichten klappen zu [#925]
- Studiengruppen: Paginierung der Teilnehmerseite [#928]
- Studiengruppen: Einladen von neuen Mitgliedern [#929]
- $_include_additional_header wird überschrieben [#930]
- keine Videovorschau im Dateibereich [#931]
- StEP00161: Installation von Plugins aus dem Repository [#934]
- Design des createQuestion() Dialogs verbessern [#938]
- Neue Navigation: Erster Klick auf Homepage/Tools führt in Adminbereich [#939]
- user_data.val nur bei Änderung zurückschreiben [#940]
- Rollenzuweisung beim Instalieren von Plugins [#945]
- Log-Aktionen USER_CHANGE_PASSWORD und SEM_CHANGE_TURNUS fehlen [#947]
- Inflation von unsichtbaren Nutzern auf wer-ist-online [#948]
- "Einladen von neuen Mitgliedern" findet zuviel [#949]
- "Einladen von neuen Mitgliedern" Link funktioniert nicht [#950]
- Zeiten/Räume - Hover bei Überschriften [#955]
- Sortierung der Studiengruppenteilnehmerseite ist momentan nicht sinnvoll [#956]
- Gruppen/Funktionen in Einrichtungen zuordnen [#959]
- PHP 5.3: flexi template factory benutzt ereg() [#973]
- Fehler bei Migration 53 [#979]
- Exception in StudipStudyArea wird nicht richtig abgefangen und behandelt [#982]
- Messagebox Icons und Farben passen nicht [#991]
- Leere Empfängerliste beim Versand interner Nachrichten an vorläufige Teilnehmer [#997]
- Studiengruppen: Es fehlen einige Statusmeldungen [#1008]
- JS Fehler bei neuer News [#1011]
- IE holpert bei Systemplugins [#1012]
- Mail Notifications werden nicht verschickt [#1028]
- Fehlerbehandlung im StudipController ist unvollständig [#1033]
- VA kopieren schlägt fehl, wenn Plugins in der VA aktiviert sind [#1034]
- Navigation springt aus der Administration einer Veranstaltung [#1039]
- Studiengruppenmoderatoren können keine News anlegen [#1042]
- JS-Effekte im Dateibereich funktionieren nicht im IE 6 + 7 [#1043]
- Messagebox in der Pluginverwaltung anzeigen [#1052]
- Plugins in Einrichtungen werden nicht angezeigt [#1059]
- Studiengruppen: Rechte zuordnen [#1086]
- Bearbeiten von Gruppengründern im IE [#1097]
- Beim Klick auf Bearbeiten einer News wird nicht die richtige Navigation aktiviert [#1105]
- Hilfe in Plugins verweist auf generische Stud.IP-Hilfe [#1106]
- Sortierung von Themenordnern im Dateibereich ist unschön [#1116]
- Fehlermeldung bei Zugriff auf Homepage [#1118]
- Fehlermeldung bei "direkt zur Veranstaltung" [#1119]
- Wiki hängt sich auf bei Benutzung des Archivierungsplugins [#1124]
- Globale Nutzeradministration: keine Änderung möglich, wenn auth_plugin != 'standard' [#1125]
- Trails: error_handler versucht auf protected-Variablen zuzugreifen [#1126]
- Flexi-Templates stolpern über backup-Dateien [#905]
- Trails sucht app/controllers/default.php [#536]

# 20.04.2010 v 1.10.2

http://develop.studip.de/trac/query?milestone=Stud.IP+1.10.2

- Request-Parameter für Sortierkriterium absichern [#1114]
- xss Lücke Ressourcenverwaltung [#1123]
- INST_USER_* - Actions werden nicht geloggt [#71]
- Evaluations-Verwaltung erzeugt ungültiges HTML [#189]
- Löschen aller Studienbereiche einer Veranstaltung möglich [#330]
- Zeilenumbrüche und HTML-Tags beim Export [#574]
- Nachträgliches Löschen von Kontingenten [#587]
- Lernmodulschnittstelle läßt sich mit IE nicht bedienen [#801]
- Fehler "unknown type of assign_user_id" in raumzeit.php [#964]
- PHP 5.3: ereg(i)(_replace) und split(i) entfernen [#974]
- Teilweise Ausgabe in falscher Sprache [#984]
- Fehler bei Suche in leerem Studienbereich [#990]
- Suche nach Ressourcen [#993]
- sendfile.php deklariert "Accept-Ranges: bytes" [#994]
- Stundenplan wird nicht angezeigt [#995]
- Layoutfehler bei der 2. Reiterebene [#996]
- Falscher Dialog bei Meine Veranstaltungen [#999]
- Titel für Nachricht beim Eintragen/Hochstufen in eine(r) Veranstaltung [#1017]
- News auf persönlicher Homepage als Autor nicht möglich [#1018]
- Falscher Tooltip bei der Bearbeitung von freien Datenfeldern [#1023]
- Gruppengründer bei Studiengruppen löschen [#1037]
- Reload beim Anlegen von Studiengruppen [#1038]
- Stud.IP im Stud.IP-News-Feed bei myuos ist überdimensioniert [#1044]
- Feld "Mailboxüberprüfung deaktivieren" wird nicht angezeigt [#1050]
- fehlerhafte Anzeige bei chronologischem Anmeldeverfahren [#1051]
- Fehlermeldung beim Archivieren [#1063]
- Austragen aus inst_user durch url-hacking [#1070]
- Nutzernamen und Freitextfeld in der Belegungsübersicht fehlt noch bei den Semesterübersichten [#1080]
- Tooltips für Suche in Einrichtungen / Vorlesungsverzeichnis [#1082]
- Losdatum kann nach dem Losen geändert werden, bewirkt aber nichts [#1084]
- PHP 5.3: trying to execute an empty query [#1094]
- Ressourcenverwaltung -> globale Rechte verwalten -> fehlerhafter Status [#1095]
- htmlReady(get_fullname(..., ..., true)) [#1098]
- Fehler bei Pagniation der Studiengruppen [#1101]
- Keine Datenfelder bei Veranstaltungen ohne Bereich [#1107]
- Log -> Veranstaltungsname [#1110]
- Javascript-Fehler auf edit_about.php [#1115]
- Ressourcenverwaltung > globale Rechte verwalten: HTML-Entity im Tooltip [#1103]

# 19.02.2010 v 1.10.1

http://develop.studip.de/trac/query?milestone=Stud.IP+1.10.1

- Fehler bei Zusatzangaben [#579]
- News: Dozent darf News seines Tutors nicht löschen [#693]
- Tutorsortierung auf admin_seminare1.php kaputt [#792]
- Falsche Meldung beim Anlegen von Blockveranstaltungsterminen [#802]
- Veranstaltungshierarchie: Erfolgsmeldung "0 Veranstaltungen eingetragen" [#825]
- Studiengruppe - Fehlermeldung bei Klick [#831]
- SimpleORMap speichert Metadaten der Tabelle in jedem Objekt [#857]
- Zugriff auf Wiki-Seiten archivierter Veranstaltungen schwer zu finden [#859]
- Fehlermeldung beim vorläufigen Eintrag in VA [#861]
- PHP 5.3: It is not safe to rely on the system's timezone settings. [#863]
- PHP 5.3: Call to undefined method StudipAuthSSO::StudipAuthSSO() [#864]
- PHP 5.3: Fehler bei call_user_func() und call-by-reference [#865]
- Datenfelder der DataFieldEntry Klasse haben einen nicht lokalisierten Titel [#866]
- PHP 5.3: get_class() expects parameter 1 to be object [#867]
- unzureichende Kommentierung im editierbaren Impressum [#868]
- Titel Ablaufplan wird nicht richtig "beschnitten" [#870]
- Nutzerdomäne setzen beim Anlegen eines Nutzers [#873]
- Defekte Umlaute bei Autovervollständigen [#878]
- Fehlermeldung bei Pluginzugriff [#880]
- Ajax Schnellsuche funzt nicht bei plugins/trails [#884]
- PHP Fatal error in content_element.php [#889]
- bei Ausgabe der Config-Werte fehlt htmlReady() [#890]
- Mail versenden via SMTP schlägt fehl [#892]
- Zugriff auf gelöschte Tabelle seminar_lernmodul [#893]
- Fehlende Titel bei Icons in der Gruppenverwaltung [#894]
- Reiter-Überschrift "Studiengruppe suchen/hinzufügen" [#896]
- StudipMail Klasse verschluckt multiple Adressaten [#898]
- Fehlermeldung beim Aufrufen der Seminar.class.php [#903]
- Studiengruppe, studygroup_dozent, admin-seite [#904]
- Fehler als root beim Verwalten von Studiengruppen [#906]
- Studiengruppe Module deaktivieren [#907]
- Forumsbeiträge sollen nicht mehr gecached werden [#912]
- Fehler unter "meine Studienmodule" [#914]
- Fehler im Link "zu Buddies hinzufügen" [#916]
- Personalseite ohne Telefonnummern [#917]
- Nachrichten-Attachments können nicht gelöscht werden [#922]
- Reihenfolge von Lehrenden in einer Veranstaltung (Grunddaten) [#933]
- Eintragen aus Warteliste funzt nicht mit IE [#935]
- Design bei Messagebox im Wiki kaputt [#944]
- Call to undefined method PDO::fetchColumn() in StudipAdmissionGroup::setMinimumContingent() [#957]
- Sperre mehreren Veranstaltungen schlägt fehl [#958]
- Fatal Error bei Benutzung von StudipMail::setBodyHtml() [#961]
- HTML-Entities in zweiter Reiterebene in der englischen Übersetzung [#971]
- PHP 5.3: funktion split in phplib.local.inc.php [#972]
- PHP 5.3: cli-Skripte brechen bei E_DEPRECATED-Meldungen ab [#980]
- Fehler: group_by_field not found in result set! [#981]
- StudipForms hat Probleme in Plugins [#983]
- studygroup_dozent taucht bei Autovervollständigung auf [#881]

# 20.11.2009 v 1.10

http://develop.studip.de/trac/query?milestone=Stud.IP+1.10

- Seltsame Meldung nach automatischem Logout [#662]
- Raumanfragen werden bei teilweiser Auflösung geschlossen [#225]
- Raumanfragen tauchen nicht auf (Sortierung: dringendere zuerst) [#524]
- Aufruf von Einrichtungsmodulen nicht mehr möglich [#563]
- StEP00150: Studentische Arbeitsgruppen [#677]
- Valides HTML auf der Login-Seite [#128]
- [nop] unterdrückt nicht [quote] [#241]
- Dozenten können sich nicht in Fort- und Weiterbildungsveranstaltungen einschreiben [#263]
- Selbst-Austragung des Teilnehmers aus Gruppe geht nicht [#301]
- Interne Nachricht verschicken bei Eintrag durch den Dozenten [#307]
- Eindeutiger Teilnehmerimport über Nutzernamen [#308]
- StEP00157: Erweiterung und Überarbeitung des Rollenmanagments [#403]
- StEP00148: Abweichende Bezeichnung für "DozentIn" und "TutorIn" [#419]
- Eigene Veranstaltungen werden nicht im Stundenplan angezeigt [#433]
- AbstractPluginIntegratorEnginePersistence & Co. entfernen [#436]
- action_id von INST_CREATE entspricht nicht der Konvention [#448]
- Ablaufdatum für Benutzer [#473]
- Anzeige des Validationkeys in der Usermaske [#474]
- Keine Veranstaltungsauswahl bei Personensuche für Admins [#481]
- Sicherheitsabfrage beim Löschen von Accounts [#487]
- register_globals für trails-Anwendungen deaktivieren [#490]
- Personensuche ist verwirrend [#500]
- Log-Aktion SEM_CHANGED_ACCESS wird nicht richtig formatiert [#505]
- Deutliche Kennzeichnung öffentlicher Veranstaltungen [#507]
- createQuestion in visual.inc.php auslagern [#528]
- Einsprung in ein Forenthema ohne Deaktivierung der Auto-Shrink-Engine [#531]
- Infobox und Hilfelink für Nutzerdomänenverwaltung [#534]
- Links verlinken im Beschreibungstext einer Veranstaltung [#543]
- Email -> Sortierung von Teilnehmern [#544]
- Signatur in Wikibeiträgen (~~~~) [#548]
- StEP00159: Erweiterung der Datenfelder um einen neuen Typ Link [#549]
- keine Fehlermeldung beim Hochladen einer leeren Datei [#552]
- AutoComplete beim Nachrichten schreiben benutzbarer machen [#559]
- Request-Klasse für Stud.IP [#561]
- Raumanfragen löschbar bei der Raumanfragenauflösung [#566]
- "Messaging anpassen" Link führt auf die Startseite [#568]
- Ungelesene News automatisch aufgeklappt beim betreten einer VA oder Einrichtung [#569]
- Textformatierungstoolbar für das Forum [#570]
- Fehler bei Registrierung am Stud.IP Entwicklungssystem [#586]
- Default Value beim erstellen einer neuen Veranstaltung [#593]
- StEP00162: Statusmeldungen erneuern und erweitern [#594]
- Fehler bei der Suche in der globalen Nutzerverwaltung [#596]
- Meine Veranstaltungen -> erweiterte Übersicht entfernen [#605]
- Layout-Fehler unter Admin > Tools > Lernmodul-Schnittstelle [#613]
- Datenschutz: Fehlermeldung bei unsichtbarer und nicht vorhandener HP unterschiedlich [#628]
- messagingSettings.inc.php inkludiert seminar_open.php [#630]
- Entfernen der Logout-Seite [#631]
- Forums Schnellansicht defekt [#632]
- Löschen Button bei Gruppenverwaltung (Adressbuch) ohne Sicherheitsabfrage [#633]
- Plugins können nicht mehr für Veranstaltungen aktiviert werden [#648]
- XML Export von Veranstaltungen wahlweise beschränken auf Heimateinrichtung [#649]
- Sammel BIEST für L10N Fehler (gettext) [#653]
- Infobox und rotes Sternchen für Studienbereich im VA-Assi [#655]
- StEP00170: Schnellsuche (für Veranstaltungen) [#657]
- memory_limit .htaccess.dist zu niedrig [#659]
- Warning unter Admin/Tools/Export [#661]
- Export von Tutoren auf templatebasierten, externen Seiten der Veranstaltungsdetails [#671]
- Zitieren verschluckt erstes Zeichen des Zitats [#672]
- Keine Statusmeldungen in der Infobox [#676]
- StEP00172: Entfernung Schnittstelle Ilias 2 [#682]
- StEP00171: Erweiterung der Raumplanung [#683]
- StEP00169: Erweiterung der Anzeige von Ressourcengruppen [#684]
- Usability-Probleme bei "Belegebung erstellen/bearbeiten" in der Ressourcenverwaltung [#685]
- Ressource erstellen -> Warnings nach dem Ändern der Art der Wiederholung [#688]
- StEP00168: "Avatare" für Veranstaltungen [#689]
- Benachrichtigungen über Kurse in die man gar nicht eingetragen ist [#692]
- Pagination der score.php [#698]
- StEP00174: Interfaces für Plugins (Komponenten) [#699]
- StEP00152: Urheberrecht und Stud.IP [#700]
- 0% bei Evaluation [#702]
- PDOException bei Impressum->Statistik [#703]
- Nachrichtenversand verweist auf falsche Seite [#710]
- Speichern einer Belegung löscht Formulardaten im Falle eines Überschneidungsfehlers [#712]
- Nicht expandierte regelmäßige Einträge führen zu Problemen bei der Raumplanung [#714]
- Warnmeldung bei Liste der Einrichtungsmitarbeiter unterdrücken [#717]
- Warnmeldung beim Anmeldung für eine Veranstaltung abfangen [#719]
- ExcelExport der laufendenen Anmeldeverfahren zeigt Losdatum bei chronologischer Anmeldung [#724]
- Externe Seiten: Marker für local und domain part von E-Mail-Adressen [#726]
- kopieren von Konfigurationen funktioniert nicht [#739]
- Anzeigefehler unter Zeiten - Räume [#743]
- Download aus Gruppenordner möglich, ohne Mitglied der Gruppe zu sein [#744]
- XSRF Lücke beim Abmelden von Veranstaltungen [#745]
- Mitgliedschaft wird nicht angezeigt [#746]
- Überflüssige Dateien [#747]
- Rechtschreibfehler [#749]
- Defektes Tabellen-Layout auf "Meine Veranstaltungen" [#750]
- Keine Fehlermeldung nach erfolglosem Buchungsversuch [#752]
- Checkbox "Raumdaten einblenden" bei Veranstaltungssuche (für Admins) hat keine Wirkung [#753]
- Array-Warning auf Nutzerdaten->Einrichtungen [#755]
- Weißer Hintegrund für Bild bei generischer Infobox [#756]
- Ein HTML-Entity wird nicht richtig angezeigt [#758]
- Wenn Roots oder Admins eine Studiengruppe anlegen ist kein Gruppengründer eingetragen [#759]
- "Bitte auswählen" beim Erstellen von Veranstaltungen [#760]
- Beim Ändern einer unregelmäßigen Zeit bleibt die Raumbuchung erhalten [#761]
- Newskasten - Background fehlt [#762]
- Im IE wird unter Gruppen/Funktionen zentriert [#763]
- Größenbeschränkung bei Attachments fehlt (StEP00155) [#766]
- Darstellung von Tutoren-Daten auf Externen Seiten Fehlerhaft [#767]
- Datei an Stud.IP-Nachricht anhängen - Infos fehlen [#769]
- Link zur pers. Homepage ist nicht vollständig bei Verwendung von userinfo-Markup im Impressum [#770]
- Nicht internationalisierte Strings... [#771]
- Caching der Plugin-Rollen defekt [#772]
- Ausgabe der Institutsdaten absichern [#773]
- StEP00171: Semesterauswahl grenzt nicht korrekt ein [#775]
- Bearbeitung von Zugangsberechtigungen funktioniert nicht in IE7 [#778]
- Sprung von der Suchseite zur Studiengruppe falsch, wenn man schon Mitglied ist [#782]
- Teilnehmerseite bricht komisch... [#783]
- Bezeichnung im Forum der Studiengruppe nicht angepasst [#785]
- Meine Veranstaltung - Bei Sortierung nach Gruppen wird die Titelzeile zu hoch [#786]
- Pakete mit mehreren Plugin-Klassen werden nicht korrekt registriert [#788]
- verwirrende Meldung bei unsichtbaren Nutzern [#789]
- Trails_Flash funktioniert nicht in Plugins [#794]
- Konfigurationen vom Typ "Veranstaltungen (Tabelle)" fehlen [#795]
- svn:ignore-Einträge fehlen für neue Ordner unter public/pictures [#796]
- Zu viele Gruppengründer bei den Studiengruppen [#798]
- Benutzername beim Lösch-Popup [#800]
- Lernmodulschnittstelle läßt sich mit IE nicht bedienen [#801]
- Personensuche sortiert nur nach Nachnamen [#805]
- Darstellungsprobleme auf der Teilnehmerseite der Studiengruppen [#806]
- Raumanfragen sind falsch sortiert (Sortierung: dringendere zuerst) [#807]
- Anmelde- und Wartelisten Einträge werden im IE zentriert. [#809]
- "Studiengruppen suchen/hinzufügen" wird unübersichtlich bei vielen Studiengruppen [#810]
- Personensuche zeigt auch Studierende der Einrichtung [#811]
- Studiengruppen-Gründer können nicht mehr zu Tutoren befördert werden [#813]
- Hochladen von externen Konfigurationen schlägt fehl [#819]
- Externe Seiten berücksichtigen Baumstruktur von Gruppen/Funktionen nicht [#820]
- Ablaufplan zentriert im Internet Explorer [#824]
- Übersicht aller Veranstaltungen eines Studienbereichs funktioniert nicht [#827]
- ajaxified_news_has_permission() liefert falsche Berechtigung [#829]
- Löschen (archivieren) von Veranstaltungen vergisst Ressourcen und verknüpfte Lernmodule [#830]
- Export von Veranstaltungen als csv ohne Teilnehmeranzahl [#839]
- Studiengruppen tauchen in der Exportauswahl auf [#840]
- festen SEM_TYPE und SEM_CLASS für Studiengruppen entfernen [#842]
- Fehler bei Ausgabe von generischen Datenfeldern auf templatebasierten externen Seiten [#843]
- Migration für StEP00157 führt zu Inkonsistenzen in roles_plugins [#846]
- Studiengruppen werde nicht richtig gelöscht [#848]
- _include_additional_header kommt zu früh [#856]
- Sortierungseinstellungen (bei Veranstaltungen) speichern [#650]

# 18.11.2009 v 1.9.1

http://develop.studip.de/trac/query?milestone=Stud.IP+1.9.1

- Navigation verschwindet in der Ressourcenverwaltung [#751]
- Newsverwaltung: Admin und Root sehen keine ausgewählten Bereiche [#589]
- Benachrichtigungen über neue Inhalte in Veranstaltungen per Mail funktioniert nicht korrekt [#333]
- Raumzeit: Widersprüchliche Ausgaben [#492]
- Es können Raumanfragen ohne Eigenschaften erstellt werden [#499]
- Automatisches Nachrücken aus Wartelisten nach Heraufsetzen der Teilnehmerzahl funktioniert nicht immer [#585]
- Objekt Typ 0 [#607]
- Anlegen von ungültigen Einzelterminen möglich [#695]
- Blockveranstaltungen anlegen, Art der Veranstaltung ändert sich bei freie Raumangabe [#854]
- Fehlerhafte Verlinkung bei Veranstaltung -> Gruppen/Funktionen verwalten [#855]

# 22.04.2009 v 1.9

http://develop.studip.de/trac/query?milestone=Stud.IP+1.9

- Neue Studienbereichsauswahl erlaubt Änderung in beliebigen Veranstaltungen [#338]
- Auto-Vervollständigen-Suche nach Personen findet keine Namen mit Sonderzeichen [#62]
- `<base>` tag für plugins.php entfernen [#164]
- Forum: Zitat eines Quote in einem Quote (BIEST00295) [#208]
- Funktionen zum Sortieren von Dozenten- und Tutorenarrays redundant [#237]
- Nutzerbilder auf  "Wer ist online?" [#252]
- Sonderzeichen in RSS-Feeds werden nicht angezeigt [#254]
- StEP00138: Studienbereichzuordnung [#264]
- style.css [#272]
- Fehlerhafter Tabellenaufbau auf der "Wer ist Online" [#273]
- Erweiterte Wiki-Link-Syntax [#275]
- Safari / Konqueror zeigen bei Einrichtungsdaten auf der persönlichen Homepage Spalten zu schmal an [#276]
- Windows-1252 Zeichen in exportierten RSS-Feeds werden nicht dargestellt [#284]
- Löschbestätigung bei Terminen mit Raumbuchung [#291]
- Nachrichten können den Status "wichtig" haben [#300]
- Kommentar/Beschreibung erlaubt Formatierung auf externen Seiten [#310]
- PDO-basierter Chat [#311]
- Fehler beim Verwalten von Veranstaltungen Reiter: Studienbereich [#334]
- Formatierungsfehler beim Bearbeiten von Beiträgen [#335]
- Quoting-Fehler auf der Seite new_user_md5.php [#336]
- Einstellen eines LDAP-Filters ermöglichen (StudipAuthLdap) [#339]
- Optimierung der Seitenladedauer [#342]
- Einfaches Versenden von Nachrichten an alle betreffenden Plugins [#343]
- Autoren bekommen automatisch Tutorenrechte [#344]
- Aktuelle Seite als Teil des Seitentitels [#347]
- Globale Nutzerverwaltung optional nur root zugänglich machen [#348]
- svn:ignore-Property für dynamische Verzeichnisse [#349]
- Chat unter IE7 geht nicht [#354]
- Anlegen von neuen Nutzern: Formular nach Anlegen/bei Fehler erneut anzeigen [#359]
- PHP Warnmeldung auf public/inst_admin.php [#361]
- Ressourcensuche - Gewünschte Belegungszeit - Unterscheiden zwischen Einzeltermin und Semestertermin [#362]
- Warnmeldung beim Kopieren einer Veranstaltung [#363]
- Zeit- und Raumdaten in der Admin-Suche einblendbar [#364]
- einstellbare Rechtestufe zur Bearbeitung von Zusatzangaben und Sperrebenen [#365]
- bessere Formulierung für "kein Raumwunsch" [#371]
- UTF-8 kodierter Text im Quellcode [#373]
- Umstellen des Auth-Plugins eines Nutzers durch den Administrator [#374]
- Anpassung von public/datenschutz.php an neues Design [#376]
- Rechte an Räume werden an Admins "vererbt" [#379]
- Download hinterläßt temporäre Dateien [#381]
- Separates Schalten für die Zuordnung zu Einrichtungen [#387]
- Zusammenlegung von Standard- und Expertenansicht im Messaging [#391]
- Größenbeschränkung für die Bildskalierung heraufsetzen [#394]
- Zusätzliche Logging-Aktionen [#395]
- StEP00149: Update-Funktion für Plugins [#396]
- PHP-Warnung in sem_portal.php [#397]
- Fehlerhafte Migration 35 [#399]
- Warnmeldung auf Teilnehmerseite unterdrücken [#401]
- Falsche Darstellung Studienbereichsauswahl (IE) [#404]
- Log-Events mit Aufbewahrungsdauer "0" werden sofort gelöscht [#408]
- Usernamen für Avatar-Bilder verfügbar machen [#409]
- Auswahl von Veranstaltungsklassen auf Personendetailseite der externen Seiten [#411]
- Externe Seiten: In der Konfigurationsoberfläche werden Checkboxes nicht richtig verarbeitet. [#412]
- Identität von unsichtbaren Nutzern kann näherungsweise ermittelt werden [#414]
- Angabe einer Zielversion für den Web-Migrator [#415]
- Anlegen einer externen Konfig. für Mitarbeiterdetails (templatebasier) gibt Fehler [#418]
- StEP00153: Abbildung von Modulstrukturen über Veranstaltungshierarchie und Plugin [#420]
- Seminar_Perm cached die Permissions nicht korrekt [#422]
- Einheitliche Bezeichnung für Step 145 [#426]
- Fehler beim Logging von Terminänderungen [#440]
- admin_log.php ist sehr langsam [#441]
- Evaluation: Freitextvorlagen können nur von root erstellt werden [#455]
- Layout auf Persönliche Homepage -> Nutzerdaten [#456]
- Fehlermeldung beim Bearbeiten des Impressums [#457]
- Sortierung der Treffer bei der Studienbereichsauswahl [#459]
- Gefundene "Zweige" bei der Studienbereichsauswahl nicht hilfreich [#460]
- Falsche Links bei der Anzeige von Log-Events [#472]
- Auswahl von Veranstaltungsklassen für externe Seiten nicht möglich [#480]
- Nicht mehr änderbare Einträge "Wo ich studiere" [#484]
- Administrationsplugins: falscher aktiver Reiter [#486]
- Beim Klicken auf das Admin-Schloss wird das Suchergebnis nicht zurückgesetzt [#495]
- Link zur Log-Anzeige aus der globalen Nutzerverwaltung geht nicht mehr [#496]
- verrutschtes Formular in der Personensuche [#497]
- Passwortfelder bei LDAP-Accounts [#509]
- Fehlender Seitentitel bei Studienbereichsauswahl [#518]
- Keine Veranstaltungen beim Durchklicken durch den Einrichtungsbaum gefunden [#526]
- content_seperator2 ist deprecated [#532]
- Reste des alten Impressums [#537]
- JavaScript und Bildschirmauflösung bei SSO [#95]
- Spezialbehandlung für lighttpd verbessern [#389]
- Externe Seiten: Auf Admin-Seite wird ein Anker falsch gesetzt [#413]
- Terminart im Ablaufplan anzeigen [#321]

# 16.04.2009 v 1.8.2

http://develop.studip.de/trac/query?milestone=Stud.IP+1.8.2

- Anmeldeverfahren: Angezeigte Kommentare wiedersprüchlich [#73]
- Forumsbeiträge können in nicht vorhandene Foren verschoben werden [#74]
- Automatisches Logout nach dem Anmelden [#93]
- Falsche Links bei Mail-Adressen in Anführungszeichen [#190]
- Literatursuche: Kein tooltip auf Info-i bei "Verknüpfung" (BIEST00320) [#203]
- Druckansicht Meine Veranstaltungen (BIEST00314) [#207]
- Zuklappen von Rollen bei "Gruppen/Funktionen" in Einrichtungen nicht möglich [#425]
- Ausblenden von Terminen im Stundenplan funktioniert nicht [#443]
- Beim Anlegen von Veranstaltungen werden unter Umständen falsche Daten angelegt [#447]
- Fehlerbehandlung für Plugins verwendet nicht den default exception handler [#449]
- Anzeige der vorhandenen Antwortmöglichkeiten funktioniert nicht [#450]
- SemesterData::getNextSemesterData() gibt u.U. falsches Semester zurück [#453]
- reiter-Klasse sollte htmlReady() übernehmen [#454]
- Fehler beim Anzeigen von Log-Events für Plugins [#458]
- "laufende Anmeldeverfahren" zeigt Losdatum bei chronologischer Anmeldung [#461]
- Anmeldeverfahren - Kontingentierung [#465]
- Wiederholungstermine lassen sich nicht in Einzeltermine auflösen [#467]
- Sperrregeln lassen sich nicht mehr löschen [#468]
- Bearbeiten von Einrichtungsmodulen geht nicht [#470]
- Link von der Gruppenverwaltung in Einrichtungen zur Hilfe "tot" [#475]
- SHOW_TERMS_ON_FIRST_LOGIN inkonsistent [#477]
- Personen tauchen fälschlicherweise in "keiner Funktion zugeordnet" auf. [#478]
- Bereiche für News sind nicht sortiert [#479]
- check_ticket() benötigt register_globals=on [#489]
- phpCAS bringt eigenes domxml-php4-to-php5 mit [#498]
- Es können Raumanfragen ohne Eigenschaften erstellt werden [#499]
- Duplicate key error beim Löschen von Studiengängen [#501]
- Angabe bei "inaktiv" in der globalen Nutzerverwaltung ist nicht übersetzt [#508]
- Permission::hasTeacherPermissionInPOI() liefert im Konstruktor immer false [#511]
- XSS Lücken in wiki, xmlrpc und sms [#512]
- Datenbankfehler bei neuen Nutzern auf der Nachrichtenseite [#513]
- Falsche Behandlung von Anführungszeichen durch den URLHelper [#514]
- Freie Veranstaltungen: Reiter Ablaufplan liefert "Zugriff verweigert" [#516]
- Hinweistext nach Löschen einer Einrichtung aktualisieren [#519]
- Problem bei der Reihenfolge der includes? [#529]
- Mitarbeiterliste einer Einrichtung zeigt keine Daten [#530]
- Nutzerdomänen mit Nutzern können gelöscht werden [#533]
- Root kann E-Mail nicht unter Nutzerdaten ändern-> Fehlermeldung bei anderen Aktionen [#540]
- unregister_globals() entfernt $user und $perm [#542]
- Empfängerliste wird nicht zurückgesetzt [#546]
- Nettere Fehlermeldungen bei E-Mail aktivierung [#551]
- Gesperrte Veranstaltungen können keinen Dozenten hinzugefügt bekommen [#558]
- Freie Raumangabe zu regelmäßiger Zeit  wird nicht mit kopiert [#564]
- Nachrichtensystem: Nicht mehr als zwei Empfänger möglich bei Antwort/Zitieren [#565]
- SQL-Fehler beim Aufruf der Ressourcenübersicht [#567]
- hochgeladene Nutzerbilder werden nicht gelöscht [#556]

# 13.02.2009 v 1.8.1

http://develop.studip.de/trac/query?milestone=Stud.IP+1.8.1

- Adminstatus geht kaputt beim Ändern von Einrichtungsdaten [#278]
- Ändern von Allgemeinen Daten lässt Private Daten verschwinden und umgekehrt [#304]
- Installieren eines Plugins löscht ZIP-Datei des Plugins [#182]
- Permission::hasTeacherPermissionInPOI() verwendet falsche Seminar ID [#184]
- Usability Bug globale Einstellungen -> Konfiguration (BIEST00069) [#205]
- foreach()-Problem in der Raumverwaltung (evaluate_values.php) (BIEST00125) [#212]
- mehrere Statusgruppen gleichen Namens möglich (BIEST00234) [#217]
- "(mehr)" auf sem_portal.php wird nicht übersetzt. [#262]
- URLHelper und Trails mögen sich doch nicht? [#267]
- freie Ortsangabe bei Veranstaltungen ohne Termine wird nicht angezeigt [#270]
- Wording: "keine Raumangabe" statt "kein gebuchter Raum" [#277]
- Fehlende Überprüfung auf aktiven Admin-Status [#280]
- Fehlerhafter Buchungsüberlappungscheck wenn Semesterstart nicht Montag [#281]
- Belegungsplan zeigt bei Buchung einer Ressource über Monatsgrenze nur 1. und letzten Buchungstag an. Dazwischen bleibt Ressource buchbar. [#282]
- Fehlende Anzeige von Gruppen/Funktionen auf der about.php [#288]
- Raumanfragen sollen nur Termine vom Typ "Sitzung" berücksichtigen [#294]
- Wochenendtermine rot markiert beim Auflösen von Raumanfragen [#295]
- Einrichtungen - Veranstaltungs-Timetable - Anzeigen von versteckten VAs für Admins [#303]
- Reiter "Lernmodule" fehlt in der Navigation [#305]
- Anzeige bereits gebuchter Räume im Auflösen-Dialog [#306]
- Option "Alle Nutzer können Ordner erstellen" hat unerwünschte Nebeneffekte [#312]
- Deaktivieren der Dateiordnerberechtigungen gibt allen Nutzern Rechte zum Erstellen von Ordnern [#313]
- Fehlender Titel beim Export (iCalendar) von Terminen [#314]
- Performance-Probleme bei der Veranstaltungssuche [#316]
- Plugin-Update führt auch das dbscheme aus [#317]
- Admins dürfen unsichtbare VAs ihrer Einrichtungen nicht exportieren. [#318]
- Ablaufplan -> andere Termine zeigt vorhandene Termine als gelöscht [#319]
- PluginEngine linkt nur auf aktive Homepage [#320]
- Standardmäßig "Alle Semester" ausgewählt [#322]
- Druckansicht / Archivieren enthält keine Terminbeschreibungen (Themen) [#323]
- Einsprung zur Terminbearbeitung klappt nicht, wenn Termin zu einer Metazeit gehört [#324]
- Quoting-Fehler bei Nachricht an den Raumadministrator [#325]
- Druckansicht Seminar - Vorbesprechung und Erster Termin haben HTML-Link zum Raum [#326]
- Option "Schreiben nur mit Passwort" geht verloren [#327]
- Einrichtungen - Gruppen/Funktionen - Personensuche - Sprungmarke fehlt [#328]
- Falsche Links zu Studiengang/Einrichtungszuordnung [#329]
- Probleme mit führenden Leerzeichen bei der Textformatierung [#331]
- Skriptabbruch in admin_room_requests [#332]
- Benachrichtigungen über neue Inhalte in Veranstaltungen per Mail funktioniert nicht korrekt [#333]
- Regelmäßige Zeiten in der Adminansicht unsortiert [#337]
- neue Forensuche findet zu viele Beiträge [#340]
- Quoting-Fehler in der Forensuche [#341]
- Portalplugins werden manchmal doppelt angezeigt [#346]
- Tabellenanordnung bei Personensuche [#350]
- Leere Empfängerliste bei Mails an vorläufig akzeptierte Teilnehmer [#351]
- HeaderController erzeugt Plugin-Links falsch [#355]
- Konfigurationsvariablen sind in der Anzeige nicht sortiert [#366]
- Aktivierung von Plugins geht beim Kopieren der Veranstaltung verloren [#367]
- Unnötiges "..." bei Dozentenauflistung auf externen Seiten [#368]
- Tutoren können keine vorläufig eingetragenen Teilnehmer akzeptieren [#369]
- Export der Teilnehmerliste unvollständig [#370]
- Namen von Admins auf externen Seiten zeigen ins "Leere" [#372]
- Evaluationen funktionieren nicht mehr, wenn PDO eingeschaltet ist [#375]
- Aktivierung von Modulen geht beim Kopieren der Veranstaltung verloren [#378]
- Zusatzangaben unsortiert in der Eingabemaske [#380]
- Homepage -> Nutzerdaten -> Einrichtungsdaten -> Rolle: Submit-Button funktioniert nicht im IE [#382]
- Autoren bekommen automatisch Tutorenrechte [#384]
- Bei Raumbuchungsbestätigungsmails fehlen die Angaben zum gebuchten Raum und Datum [#385]
- Tippfehler im HTML [#388]
- Beschreibung einer regelmäßigen Zeit wird nicht durchgereicht [#390]
- Anzeige auf Teilnehmerseite sortiert nur nach Nachnamen [#393]
- PluginEngine::instantiatePlugin() liefert immer das gleiche Objekt [#398]
- Admin-Zugriff auf Administrations-Plugins [#400]
- Reihenfolge der Plugins fehlerhaft [#402]
- Bei mehrfachem Anlegen von Blockterminen wird das hintere Fenster nicht neu geladen [#407]
- Vorgemerkte Termine nicht löschbar [#416]
- $rechte nicht verfügbar bei Verwendung von Layouts im Sinne von #82 [#421]
- Rechtschreibfehler auf "My Stud.IP" [#423]
- Vorbelegung der Homepage (Nutzerdaten) [#424]
- Design-Glitch bei Nutzerdatenverwaltung im IE [#427]
- Fehlende Brotkrumenleiste in der Ressourcenverwaltung [#428]
- Fehlende Sortierung in der Ressourcenverwaltung bei Räumen [#429]
- Fehlerhafter Export von Zusatzangaben [#430]
- image_proxy.php verursacht hängende Webserverprozesse [#431]
- Verknüpfte unsichtbare Veranstaltungen werden bei der Verwaltung der Studiengänge ausgeblendet [#432]
- Fehlende Beschreibung bei Anzeige von "Meine aktuellen Termine" [#437]
- Bearbeiten von Log-Aktionen klappt nicht immer [#442]
- Umsortieren von Personen unter Gruppen/Funktionen funktioniert nicht [#445]
- Beim Anlegen von Veranstaltungen werden unter Umständen falsche Daten angelegt [#447]
- Fehler beim Anlegen von Blockveranstaltungsterminen [#451]
- Rechtschreibfehler auf Homepage - Nutzerdaten [#452]
- Rechtschreibfehler "Hobbies" -> "Hobbys" [#360]

# 22.09.2008 v 1.8

- Verwendung von trac für das Projektmanagement
- Einführung von Nutzerdomänen
- Erweiterung der Nutzerdaten- und Statusgruppenverwaltung
- Admin-Link auf "Meine Seminare"
- (leicht) verbesserte Forenssuche
- Generische Datenfelder für Nutzer auf der Homepage verschieben
- Stundenplan Take 2 (ausblenden von Terminen möglich)
- Reorganisation des Upload-Verzeichnisses
- Zip Download Beschränkung
- Erweiterung Sperregeln / Umgang mit importierten Veranstaltungen
- Vereinfachte eMail-Änderung (Aktivierungslink)

# 14.09.2008 v 1.7.1

- BIEST00380: merge [9650]
- svnmerge from trunk
- svnmerge 9664: never set empty title for scm page, fixes #67
- svnmerge 9689: security fixes
- svnmerge 9690-9692: make view=reset work again, if you do this, do it correctly...
- svnmerge 9707: bugfix Zusatzangaben
- merge fix for PluginNavigation, re #75
- branch 1.7, fixed #84
- `&nbsp;` entfernt. zerlegen mal wieder das oldenburger layout. langfristiges ziel durch ein lifter: alle `&nbsp;` entfernen
- svnmerge 9756: fixes #85
- merge fix for #91
- merge URLHelper into source:branches/1.7 as requested
- merge fixes for #104 and #105
- merge fix for #108
- merge fix for #101
- svnmerge 9847: fixes #63 and #110
- svnmerge 9873: fixes #114
- svnmerge 9981: bugfix: warteliste bei los als csv
- svnmerge 10002-10004: bugfixes lockrules, bugfix: Button für prozentuale Kontingentierung ausblenden, wenn Anmeldeverfahren eingeschaltet, Passwortfelder umbenennen, fixes #154
- add missing check for $this->validate_url, fixed on trunk as part of [10029]
- svnmerge 10043-10044: fixed #79, corrected typo, refs #150
- merge fix for #163
- merge fix for #151
- svnmerge 10060: don't terminate cli scripts on php warnings
- svnmerge 10062: add new method getPluginURL() to get an absolute URL to the plugin, fixes #120
- svnmerge 10109-10110: convert errors to exceptions, refs #175, refactored image upload, refs #150
- svnmerge 10111: checking for existence of constant, refs #175
- fixed BIEST162
- refs #177, merged r10123 to 1.7
- refs #178, merged r10125 to 1.7
- fixed BIEST162
- svnmeerge 10077: using REQUEST_URI instead of PHP_INFO, fixes #168
- merged r10153
- fixes #224 for 1.7
- svnmerge 10270: fixes #245
- prevent background on index page from repeating
- svnmerge 10404: move form tags, closes #258

# 18.04.2008 v 1.7.0

- Stud.IP ist nun Shibboleth-Ready
- Erweiterung der globalen Einstellungen um Regeln zur Sperrung von Veranstaltungsdaten
- Zukünftig PDO als Standardmethode des Datenbankzugriffs
- Möglichkeit des automatischen Anforderns eines neuen Passworts durch den Benutzer
- Ajax-Erweiterung zum automatischen Vervollständigen bei der Personen- und Veranstaltungssuche
- bei Bedarf Verwendung von Nutzerbilder-Thumbnails anstelle der Originalbilder
- Erweiterung Anmeldesystem II - die Kontingentierung ist jetzt optional
- Einbindung von Flash-Movies über Schnellformatierung und im Dateibereich möglich
- Möglichkeit zum Anlegen eines Wiki-Inhaltsverzeichnises
- Beschränkung der freien Nutzeranmeldung nach Email-Adressen möglich
- Generierung von "Assets"-Verweisen vereinfacht
- Refactoring der Datei "plugins.php"

# 14.04.2008 v 1.6.0-3

- merged [8988] - refs BIEST00349, filefolders are must not be deleted when deleting a regular time
- merge r9001 to source:branches/1.6.0
- merge r9000 to source:branches/1.6.0
- merge r9042 r9035 r9036 r9039 source:branches/1.6.0
- merge r9116 source:branches/1.6.0
- merged [9282] to 1.7
- merged [9296] to 1.7, fixes BIEST00360
- bugfix: typo
- bugfix: falsche Berechnung erster Termin
- bugfix 1.6: msg.inc.php fehlt wenn Plugins ausgeschaltet

# 02.02.2008 v 1.6.0-2

- merge r8645: schlankeres Hintergrundbild
- merge r8648: weitere kleine fixes Raumzeit
- BIEST00343: Fehlendes PHP_SELF ergänzt
- updating to flexi 0.1.5
- BIEST00346: Keine Nutzerbilder in templatebasierten externen Seiten
- bugfix TeinehmerInnen
- merge r8710: fixing possible header injection
- merge r8723: Studienbereichsauswahl berücksichtigt beteiligte Einrichtungen
- BIEST00349: [8767] merged to 1.6, added missing variable to function call
- BIEST00341: [8768] merged to 1.6, added htmlReady to select-box for folders
- merge r8761 r8764 r8772\
  bugfix Bereichsauswahl News\
  Speicherverbrauch gesenkt\
  BIEST00345: Problem bei der Newserstellung als Root
- BIEST00350, BIEST00351: fixed - merge [8777]\
  removing entrys in themen_termine,\
  returning correct number of changed dates,\
  changed wording of info message
- BIEST00350: merged [8779]\
  Phantomthemen nach Ändern der regelmäßigen Zeiten / Fatal Error
- merge r8791\
  BIEST00345: Problem bei der Newserstellung als Root
- merge r8809 r8810\
  BIEST00345 fix für OL\
  bugfix Anzeige alle Dateien ohne range_id, wenn kein Ordner vorhanden
- merge [8907]\
  links in navbar don't work while login form is shown
- merge [8916]\
  links in navbar don't work while login form is shown (part 2)
- merge [8916]\
  links in navbar don't work while login form is shown (part 2)
- latex blacklist erweitert

# 02.10.2007 v 1.6.0-1

- Abbildung von Studienmodulstrukturen (Hallesche Lösung)
- Erweiterung der Teilnehmeransicht
- Introducing Safiré (Einführung eines neuen generischen Designs)
- Anzeige des Nutzernamens und des Freitexts im Belegungsplan von Ressourcen
- Darstellung von Systemsplugins auf der Startseite
- Erweiterung Anmeldesystem
- "Flexi"-bilisierung der Infobox ( = als Tempalteversion)
- Erweiterung der Gruppenfunktion / Dateiordner für Gruppen
- Erweiterungen IIlias Verknüpfung
- Erweiterung des Teilnehmerexports bzgl. Sortierung nach Anmeldedatum
- Query-Caching on Demand
- Online-Konfiguration der Typisierung generischer Datenfelder
- Suchfunktion für Wikis
- Migrations
- Vereinfachte Nachrichten an Rechtegruppen innerhalb einer Veranstaltung
- Hinzufügen von Konfigurationsoptionen für Verzeichnisse mit dynamischen Inhalt
- Externe Seiten templatebasiert
- Sperrung von Veranstaltung gegen Selbsteintrag
- Caching
- Neue Admin-Seiten für "Zeiten" und "Ablaufplan"
- weitere Detailverbesserungen und Bugfixes


# 07.07.2007 v 1.5.0-2

- r7417-7419 portiert\
  BIEST00242: Missverständlicher Eintrag in der history.txt\
  BIEST00243: Fehler und Merkwürdigkeiten in den Textstrings
- merge r7427 - r7429\
  bugfix: "Enddatum der Kontingentierung" bei chronolog. Anmeldung entschärft\
  bugfix: Druckansicht "Studienbuch"\
  bugfix: Baumadministration falsche Linien, Neueintrag Veranstaltungshierarchie
- merge r7433 - bugfix: Druckansicht "Studienbuch"
- ein " zuviel :-)
- Fixes BIEST00247 - Formatname wird in LaTex-Hash-Code mit einbezogen
- Fixes BIEST00132 - Fehler in der Weiter/Zurueck-Navigation im Event-Log
- merge r7465\
  Bugfix BIEST00246: DB-Fehler beim kopieren und importieren von Literaturlisten
- merge r7468 r7469\
  Bugfix BIEST00248: Exportverzeichnis automatisch anlegen\
  Bugfix BIEST00249: Löschen von Nutzern: Bild wird nicht gelöscht
- merge r7496 - Bugfix BIEST00252: Der Upload des Plugins ist fehlgeschlagen
- BIEST00150, BIEST00255: Probleme mit externen Seiten
- BIEST00259: Darstellungsfehler im Kalender Tagesansicht
- BIEST00260: Fehlerhafte Endzeiten von Terminen im Kalender mit PHP5
- BIEST00260, Referenzen entfernt
- Bugfix: Popupkalender beim editieren des Endatums eines Termins
- merge r7723 - kaputtes HTML bei skype info korrigiert
- merge r7691 - fehlendes >
- merge r7769 - Bugfix Archivsuche
- merge r7778 - bugfix Ressourcenrechte zuweisen

# 27.03.2007 v 1.5.0-1

- PmWiki läßt sich jetzt über die E-Learning-Schnittstelle anbinden
- Anzeige des Skype Namen und Links von Usern auf der Homepage
- Erweiterung SCM-Seite in Seminaren
- die Reihenfolge von  Dozenten in Seminaren läßt sich jetzt ändern
- bei Anmeldeverfaren lassen sich die Wartelisten deaktivieren
- Nicht abonnieren, sondern nur in Stundenplan eintragen nun möglich
- neue user management tools für Admins
- Einführung eines abstrakten Interfaces für einen Template-Mechanismus
- Umstellung der Verzeichnisstruktur und Anpassungen an PHP5
- weitere Detailverbesserungen und Bugfixes

# 18.12.2006 v 1.4.0-4

- merged r7052 (bugfix BIEST00203)
- merged r7051 (bugfix BIEST00200, nur Methoden mit 'action' präfix aufrufen)
- merged r7050 (semester_id statt Semester_id)
- merged r7049 (i18n Problem, config.inc.php darf nicht zu früh eingebunden werden)
- merged r7048 (Mysql 5 Kompatibilität)
- ASSETS_URL eingefuegt
- htmlReady eingefuegt
- portiert r7055 (BIEST00204)
- Fixed BIEST00205 - fehlerhafter Insert-Query
- R6982 portiert (bugfix sql, Anzahl der Beiträge in einem Thema)
- $UNI_INFO zu "Stud.IP 1.4 ..." korrigiert
- $SYMBOL_PATH zu "assets/images/symbol" korrigiert
- $UNI_NAME_CLEAN nach "Stud.IP 1.4" geaendert
- R6901 portiert und bugfixed (BIEST00190: Fehlerhafte Darstellung der Seite "Alle Dateien")
- bugfix falscher bildpfad
- R6910:6912 portiert
- R6908 portiert (bugfix: is_array() gegen Warning eingebaut)
- R6906 portiert (bugfix: Dateien konnten in schreibgeschützte Ordner verschoben werden)
- BIEST00194
- chgset 6896 portiert (BIEST00184: Problem bei der Zuordnung eines ILIAS-Lernmodules)
- Version auf 1.4.0-4
- chgsets 6874,6874,6876 und 6883 portiert (bugfix Literaturlistenimport, bugfix Druckansicht)
- chgsets 6868,6869 und 6871 portiert (Mysql 5 Kombatibiltät, nochmal BIEST00170)
- chgsets 6855 und 6867 portiert (Performancetweak (Speicherverbrauch),bugfix, visible Feld)
- chgsets 6841 bis 6852 portiert
- chgsets 6837 und 6853 portiert (BUGFIX "invisible", Umlautproblem mit neuerem gettext)
- chgsets 6828,6830-6836 portiert
- Ausbau StEP00063, rueckportiert aus 1.4er release
- chgset 6812 portiert (bugfix ajax chat)
- Stand ca. Version 1.4.0-3
- Umstellung von cvs auf svn

# 12.09.2006 v 1.4.0-3

- studip-htdocs/vote/vote_show.inc.php: bugfix BIEST00165 / BIEST00163 wieder entfernt

# 06.09.2006 v 1.4.0-2

- studip-1.4.0/VERSION: 1.4.0-2
- studip-htdocs/admin_seminare_assi.php: BIEST00174
- studip-htdocs/admin_statusgruppe.php: BIEST00174
- studip-htdocs/archiv.php: Bugfix: Bild-Pfad durch Assets Umbau war noch kaputt
- studip-htdocs/contact_statusgruppen.php: BIEST00174
- studip-htdocs/datei.inc.php: BIEST00174
- studip-htdocs/folder.php: BIEST00176 fixed
- studip-htdocs/forum.inc.php: BIEST00174
- studip-htdocs/functions.php: BIEST00174
- studip-htdocs/header.php: Aufbau der Location-ID der Hilfe gefixt
- studip-htdocs/index.php: BIEST00174
- studip-htdocs/phplib_local.inc.php: version=1.4.0-2
- studip-htdocs/scm.php: BIEST00174
- studip-htdocs/sem_notification.php: BIEST00177 fixed
- studip-htdocs/show_news.php: BIEST00174
- studip-htdocs/sms_send.php: BIEST00174
- studip-htdocs/visual.inc.php: NULL values should not be processed
- studip-htdocs/wiki.inc.php: BIEST00174
- studip-htdocs/write_topic.php: BIEST00174
- studip-htdocs/lib/classes/guestbook.class.php: BIEST00174
- studip-htdocs/vote/vote_show.inc.php: bugfix mrunge BIEST00165 / BIEST00163

# 25.08.2006 v 1.4.0-1

- Sichtbarkeit von NutzerInnen
- Neues Hilfe-System basierend auf einer zentralen Wiki-Seite
- Ordnerberechtigungssystemim für den Dateibereich
- alternativer Chatclient (AJAX)
- Literaturlistenimport über Plugins
- Externe Seiten: Sichtbarkeit von Veranstaltungstypen, Einschränkung der Veranstaltungsübersichten auf ausgewählte Studienbereiche
- Anzeige des Namens auf der wer-ist-online konfigurierbar, "Motto"
- Nutzerkommentare bei vorläufiger Anmeldung und Anpassung Teilnehmerexport
- Erweiterungen für Single Sign On
- Erweiterung der Plugin-Schnittstelle
- Alphabetische Sortierung für Statusgruppen
- Löschen des eigenen Homepagebildes nun möglich
- Konfigurierbare Sperre für den Nutzertitel
- Root-Funktion zum Sperren einzelner Benutzer
- weitere Detailverbesserungen und Bugfixes

# 10.06.2006 v 1.3.0-2

- studip-db/studip_default_data.sql: neue Konfigoption: EVAL_AUSWERTUNG_GRAPH_FORMAT, BIEST00128: Fehlende Einträge für PluginEngine in sql-Skripten
- studip-doc/de/studip_installation_guide.txt: Konfigurationshinweise PluginEngine
- studip-htdocs/about.php: BIEST00142 und weitere XSS Lücken gefixed, BIEST00119: Externes Anspringen von Stud.IP RSS-Feeds
- studip-htdocs/admin_admission.php: bugfix: Start-und Endzeitpunkt bei Anmeldeverfahren wurden nicht gelöscht
- studip-htdocs/admin_seminare_assi.php: bugfix: Vorbesprechungstermincheck war zu drastisch (und außerdem kaputt)
- studip-htdocs/auswahl_suche.php: Update: Kleine Umstellung: Veranstaltungssuche oben
- studip-htdocs/browse.php: bugfix, BIEST00142 und weitere XSS Lücken gefixed
- studip-htdocs/contact.php: BIEST00142 und weitere XSS Lücken gefixed
- studip-htdocs/crcregister.ihtml: BIEST00110 / reguläre Ausdrücke aus email_validation_class benutzen
- studip-htdocs/datei.inc.php: bugfix Datei aktualisieren
- studip-htdocs/dates.inc.php: bugfix: Vorbesprechungstermincheck war zu drastisch (und außerdem kaputt)
- studip-htdocs/details.php: regex vereinfacht, BIEST00142 und weitere XSS Lücken gefixed
- studip-htdocs/edit_about.php: BIEST00144 und StEP00060, BIEST00142 und weitere XSS Lücken gefixed, BIEST00110 / reguläre Ausdrücke aus email_validation_class benutzen, bugfix BIEST00113: Layout-Fehler
- studip-htdocs/elearning_interface.php: bugfix: Sortierreihenfolge von eingebundenen Objekten geaendert
- studip-htdocs/eval_summary.php: Ausgabeformat der Diagramme konfigurierbar, BIEST00116 fixed
- studip-htdocs/eval_summary_export.php: Ausgabeformat der Diagramme konfigurierbar, BIEST00116 fixed
- studip-htdocs/export.php: bugfix: direkten xml export gefixed
- studip-htdocs/folder.php: bugfix BIEST00115: Layout Glitches in der Dateiverwaltung
- studip-htdocs/forum.inc.php: bugfix BIEST00143
- studip-htdocs/ilias3_referrer.php: bugfix: aussagekraeftige Meldung bei fehlgeschlagenem Login
- studip-htdocs/impressum.php: coregroup aktualisierung
- studip-htdocs/institut_main.php: BIEST00119: Externes Anspringen von Stud.IP RSS-Feeds
- studip-htdocs/institut_members.php: BIEST00142 und weitere XSS Lücken gefixed
- studip-htdocs/links_admin.inc.php: Bugfix: Einbindung nicht per TopNavigation sondern Navigation
- studip-htdocs/meine_seminare_func.inc.php: bugfix aktuelles Semester nicht vorhanden
- studip-htdocs/my_elearning.php: Kommentarzeichen entfernt
- studip-htdocs/mystudip.inc.php: BIEST00142 und weitere XSS Lücken gefixed
- studip-htdocs/online.php: BIEST00142 und weitere XSS Lücken gefixed
- studip-htdocs/phplib_local.inc.php: Versionsnummer auf 1.3.0-2, BIEST00142 und weitere XSS Lücken gefixed, BIEST00121
- studip-htdocs/plugins.php: output buffering
- studip-htdocs/rss.php: BIEST00119: Externes Anspringen von Stud.IP RSS-Feeds
- studip-htdocs/sem_notification.php: BIEST00134
- studip-htdocs/sem_verify.php: BIEST00142 und weitere XSS Lücken gefixed
- studip-htdocs/seminar_main.php: BIEST00119: Externes Anspringen von Stud.IP RSS-Feeds
- studip-htdocs/seminar_open.php: bugfix: config.inc.php wurde zu frueh eingebunden (i18n Problem)
- studip-htdocs/show_news.php: BIEST00119: Externes Anspringen von Stud.IP RSS-Feeds
- studip-htdocs/sms_box.php: BIEST00142 und weitere XSS Lücken gefixed
- studip-htdocs/sms_functions.inc.php: htmlReady ergänzt
- studip-htdocs/sms_send.php: BIEST00142 und weitere XSS Lücken gefixed, bugfix: sms versenden an alle mitarbeiter einer Einrichtung
- studip-htdocs/statusgruppen.php: BIEST00142 und weitere XSS Lücken gefixed
- studip-htdocs/studipim.php: Tippfehler
- studip-htdocs/teilnehmer.php: zusätzliche Absicherung über Seminar_Session::check_ticket(), BIEST00142 und weitere XSS Lücken gefixed, bugfix BIEST00117: Aktivitätsanzeige ist hinüber
- studip-htdocs/visual.inc.php: BIEST00120
- studip-htdocs/calendar/lib/driver/MySQL/list_driver.inc.php: BIEST00065
- studip-htdocs/help/structure.inc.php: update Literaturhilfe
- studip-htdocs/help/switcher.inc.php: update Literaturhilfe
- studip-htdocs/lib/classes/Config.class.php: ein NOT NULL Feld sollte nicht auf den String 'NULL' gesetzt werden
- studip-htdocs/lib/classes/ModulesNotification.class.php: BIEST00129
- studip-htdocs/lib/classes/StudipLitSearch.class.php: kleine Textaenderung
- studip-htdocs/lib/classes/phplot.php: Error-Reporting auskommentiert
- studip-htdocs/locale/en/LC_MESSAGES/* : UPDATE Englisg translation
- studip-htdocs/modules/evaluation/evaluation_admin_template.inc.php: bugfix: Markierung von Systemtemplates
- studip-htdocs/modules/evaluation/evaluation_admin_template.lib.php: bugfix: Markierung von Systemtemplates
- studip-htdocs/modules/evaluation/classes/EvaluationExportManagerCSV.class.php: bugfix: export von mc Fragen
- studip-htdocs/modules/evaluation/classes/EvaluationTreeEditView.class.php: bugfix: Markierung von Systemtemplates
- studip-htdocs/plugins/core/AbstractPluginPersistence.class.php: update
- studip-htdocs/plugins/core/AbstractStudIPAdministrationPlugin.class.php: update
- studip-htdocs/plugins/core/AbstractStudIPPluginVisualization.class.php: update
- studip-htdocs/plugins/core/AbstractStudIPStandardPlugin.class.php: update
- studip-htdocs/plugins/core/AbstractStudIPSystemPlugin.class.php: update
- studip-htdocs/plugins/core/AdminInfo.class.php: update
- studip-htdocs/plugins/core/ChangeMessage.class.php: update
- studip-htdocs/plugins/core/DBEnvironment.class.php: update
- studip-htdocs/plugins/core/Environment.class.php: update
- studip-htdocs/plugins/core/HelpInfo.class.php: update
- studip-htdocs/plugins/core/Permission.class.php: update
- studip-htdocs/plugins/core/PluginNavigation.class.php: update
- studip-htdocs/plugins/core/StudIPCore.class.php: update
- studip-htdocs/plugins/core/StudIPInstitute.class.php: update
- studip-htdocs/plugins/core/StudIPUser.class.php: update
- studip-htdocs/plugins/packages/core/PluginAdministrationPlugin.class.php: update
- studip-htdocs/plugins/packages/core/PluginAdministrationVisualization.class.php: update
- studip-htdocs/resources/views/msgs_resources.inc.php: BIEST00127
- studip-htdocs/soap/lib/nusoap.php: bugfix für verbindung über https ohne curl
- studip-phplib/email_validation.inc: reguläre Ausdrücke angepasst
- studip-phplib/prepend.php: Absicherung $PHP_SELF
- studip-phplib/prepend4.php: Absicherung $PHP_SELF

# 18.03.2006 v 1.3.0-1

- RSS-Reader für Nutzer
- RSS-Feeds für alle News (neu: Veranstaltungen, Einrichtungen und Systemnews)
- neue Schnittstelle zu anderen eLearning-Systemen, insbesondere ILIAS3
- eMail-Benachrichtigung bei neuen Inhalten in Veranstaltungen
- neuer Dateiupload für mehrere Dateien per ZIP
- grafische Evaluationsauswertung
- neue Plugin-Schnittstelle
- Loggingfunktionen für wichtige Systembereiche
- Teilnehmer können aus Listen in Veranstaltungen importiert werden
- Wartungsmodus
- history.txt wird nun formatiert ausgegeben ;-)
- weitere Detailverbesserungen und Bugfixes

# 08.11.2005 v 1.2.0-2

- studip-phplib/session4_custom.inc: Bugfix gegen 'deleted' cookie
- studip-htdocs/folder.php: BIEST00045  - Dateibereich: bei defekter Verlinkung erscheint fehlerhafte Fehlerseite
- studip-htdocs/details.php: BIEST00046 - fehlender Link für Admins
- studip-htdocs/locale/de/LC_HELP/pages/ii_passwort.htm: BIEST00047 mailto fehlte
- studip-htdocs/resources/resourcesControl.inc.php: BIEST00048 und BIEST00051 - Fehler: Gruppenbelegungspläne als Dozent
- studip-htdocs/resources/views/ShowGroupSchedules.class.php: BIEST00048 und BIEST00051
- studip-htdocs/resources/views/ShowList.class.php: BIEST00048 und BIEST00051
- studip-htdocs/resources/views/ShowThread.class.php: BIEST00048 und BIEST00051
- studip-htdocs/admin_metadates.php: BIEST00050 - Raumbuchung einfach übernommen bei Semesteränderung
- studip-config/local.inc.dist: BIEST00052 - neue Konfig $ZIP_OPTIONS
- studip-phplib/local.inc.dist: BIEST00052
- studip-htdocs/archiv.inc.php: BIEST00052
- studip-htdocs/datei.inc.php: BIEST00052
- studip-htdocs/sendfile.php: BIEST00052
- studip-htdocs/header.php BIEST00059 - Abgelaufene, nicht gelöschte, ungelesene News führen zu rotem News-Icon
- studip-htdocs/meine_seminare.php: BIEST00059
- studip-htdocs/teilnehmer.php: BIEST00062 - Division by zero on line 709
- studip-htdocs/resources/lib/AssignObject.class.php: BIEST00063 - Vertauschung von Buchungen beim Auflösen einer Raumanfrage
- studip-htdocs/resources/lib/evaluate_values.php: BIEST00063
- studip-htdocs/pers_browse.inc.php: `<tr>`s ergaenzt
- studip-htdocs/calendar/lib/CalendarEvent.class.php: pictures Verzeichnis verschoben
- studip-htdocs/calendar/lib/SeminarEvent.class.php: pictures Verzeichnis verschoben
- studip-htdocs/dates.inc.php: bugfix - falsche Zuweisung
- studip-htdocs/phplib_local.inc.php New Versionnumber: 1.2.0-2
- studip-phplib/local.inc.dist New Versionnumber: 1.2.0-2
(BIEST = Bug und Inkonsitenz Erkennung für Stud.IP :-)
 Bug and inconsistency reconnaissance for Stud.IP)

# 30.08.2005 v 1.2.0-1

- neuer Zugriff per Tastenkombination auf die wichtigsten Systembereiche
- Friend-of-Friend Anzeige (Verbindungsketten zu anderen Nutzern) auf der Homepage
- RSS-Feed für private News
- News-Kommentare für alle News im System
- neue Gruppierungsfunktion der Seite "Meine Veranstaltungen" nach Farbgruppen, Semestern, Typ und Studienbereichen
- Verschieben von Dateien und Ordnern in andere Veranstaltungen oder Einrichtungen ist nun möglich
- Sichtbarkeit und Archivieren kann für Dozenten freigeschaltet werden
- Erweiterungen an der Ressourcenverwaltung: Listenansicht für Raumanfragen, Sortierung von Raumanfragen nach Dringlichkeit, Belegungsübersichten für Semester, externe Darstellung von Raumplänen, Raumgruppen und weitere Detailverbesserungen
- freie Datenfelder können nun weitergehend konfiguriert werden
- neues Sessionmanagement: Die Stud.IP-PhpLib kann nun mit PHP4-Sessions arbeiten
- neues Datenbankbasiertes Konfiguratiossystem
- Erweiterung der Schnellformatierung um Tabellendarstellung
- Wiki-Seiten werden nun archiviert
- Performance-Verbesserungen (Datenbankabfragen und -struktur)
- weitere Detailverbesserungen und Bugfixes

# 16.01.2005 v 1.1.5-3

- get_ticket() build in (anoack new_user_md5.php phplib_local.inc.php)
- mysql_escape_string() (anoack admin_news.php, forum.inc.php)
- no refresh for logout (anoack forum.inc.php)
- _(..) included (anoack edit_about.php)
- htmlReady() inserted (anoak modules/evaluation/evaluation_admin_overview.lib.php)
- SECURITY: username studip is no more allowed (anoack studip-phplib/email_validation.inc)
- New Versionnumber: 1.1.5-3 ( Philipp Huegelmeyer /studip-phplib/local.inc.dist VERSION studip-htdocs/phplib_local.inc.php )

# 30.12.2004 v 1.1.5-2

- SECURITY: URLS; that contain parameters, are not included as pictures any more (Jens Schmelzer, André Noak: visual.inc.php)
- ChangeLog in english now (Philipp Huegelmeyer)
- New Versionnumber: 1.1.5-2 ( Philipp Huegelmeyer /studip-phplib/local.inc.dist VERSION studip-htdocs/phplib_local.inc.php )

# 26.09.2004 v 1.1.5-1

- Neue Messaging-Funktionen: Betreff für systeminterne Nachrichten, erzwungene/ optionale eMail-Weiterleitung, differenzierte Lesebestätigung, Bild des Absenders in Nachrichtenansicht und verbesserte Anwort-Funktionalität
- Neue Art der Erfassung von besuchten Bereichen in Veranstaltungen, so dass nun für jeden Bereich einzeln angezeigt werden kann, ob neue Inhalte vorliegen.
- Optische Kennzeichnung von neuen Inhalten auf der Startseite (News, Evaluationen und Votes)
- Einfügen von personalisierten Kommentaren an beliebige Stellen in Wiki-Seiten ähnlich der Word-Anmerkungs-Funktion; Anzeige als Textbox oder Hover-Icon
- Darstellung von Smileys nach Kategorie und eigenen Favoriten, Statistik für die Smileyverwaltung
- Verbesserte Eingabe von Bewertungen im Forum
- Termineingabehilfe/Minikalender für die Eingabe von Terminen an vielen Stellen im System, konfigurierbare Standardzeiten zum Auswählen für regelmäßige Termine
- Verbesserte Bedienung der Ablaufplansicht und Ablaufplanverwaltung
- automatische Umsetzung von internen Links, falls mehrere Servername verwendet werden
- Intern verbesserte Benutzerverwaltung
- Zahlreiche Performance-Verbesserungen (Datenbankabfragen und -struktur)
- weitere Detailverbesserungen und Bugfixes

# 14.09.2004 v 1.1.0-6

- studip-htdocs/phplib_local.inc.php - Versionsnummer auf 1.1.0-6
- VERSION - Versionsnummer auf 1.1.0-6 (André Noack)
- studip-htdocs/sendfile.php - security bugfix, übergebene Variable $file_id wurde ohne Absicherung in einem exec() Aufruf verwendet (André Noack, Jens Schmelzer)
- studip-phplib/prepend.php - security bugfix, wegen register_globals kann das $GLOBALS Array manipuliert werden (André Noack, Jens Schmelzer)

# 19.08.2004 v 1.1.0-5

- Bugfix: Fehlende Htmlready's (Stefan Suchi, about.php, archiv.inc.php, institute_members.php, extern/modules/views/persondetails.inc.php.diff)
- Neue Versionsnummer. (Philipp Hügelmeyer, /studip-phplib/local.inc.dist  VERSION studip-htdocs/phplib_local.inc.php)

# 01.08.2004 v 1.1.0-4

- Bugfix: Parse Error (visual.inc.php, André Noack)
- Bugfix: Bugfix: $SMILE_PATH wurde nicht an allen Stellen verwendet (admin_smileys.php, Parse Error Jens Schmelzer & Cornelis Kater).
- Neue Versionsnummer. (Philipp Hügelmeyer, /studip-phplib/local.inc.dist  VERSION studip-htdocs/phplib_local.inc.php)

 21.07.2004 v 1.1.0-3

- Bugfix: monsterformatierungen (Jens Schmelzer, /studip-htdocs/visual.inc.php 2.75 -> 2.76)
- Bugfix:  delete_date(): Check ob topic_id existiert; delete_topic(): nicht löschen,wenn topic_id=0 (André Noack, studip-htdocs/dates.inc.php, 1.3-> 1.4)
- Bugfix: Beim Einfügen in die Tabelle Termine wird topic_id nicht mehr auf '0' gesetzt (André Noack, admin_seminare_assi.php 1.1 -> 1.2)
- Neue Versionsnummer. (Philipp Hügelmeyer, /studip-phplib/local.inc.dist  VERSION studip-htdocs/phplib_local.inc.php)

# 04.06.2004 v 1.1.0-2

- Bugfix: Semesterwechsel, Veranstaltungsbeginn und Löschen von Zeiten führt nun auch zum Update
- Sort ohne Array (beides Cornelis Cater, admin_metadates.php 2.40 -> 2.43)
- Merkliste - Aktualisieren-Knopf (Cornelis Kater, ClipBoard.class.php 1.2  -> 1.3)
- Die allgemeine Ortsangabe (wenn keine Zeiten und Termine vorhanden wird diese ausgegben) hat nicht funktioniert. (Cornelis Kater, dates.inc.php 2.44 -> 2.45)
-  Termine unregelmäßiger Veranstaltungen die auch ein Forenthema haben, lassen  sich nicht mehr löschen. (Cornelis Kater admin_dates.php 2.24 -> 2.25, dates.inc.php 2.48 -> 2.49)
- Darstellungsfehler in der Teilnehmerliste (Till Glöggler teilnehmer.php 2.31 -> 2.32)
- Löschen für Tutoren im Wiki (Tobias Thelen wiki.inc.php 1.20 -> 1.21)
- Neue Versionsnummer. (Philipp Hügelmeyer, /studip-phplib/local.inc.dist  VERSION studip-htdocs/phplib_local.inc.php)

# 17.05.2004 v 1.1.0-1

- Evaluations und Umfragemodul zur Erstellung empirischer Online-Fragebögen
- Möglichkeit der Dateiverlinkung auf geschützte Dokumente auf externen Servern
- Administrationsebene für Literaturnachweise mit integriertem Bibliothekscheck
- Erweiterte Raumverwaltung für zentrale Raumvergabe über Raumwünsche
- Funktion zum Kopieren von Veranstaltungen
- Mechanismus zum Verstecken und Sichtbarmachen von Veranstaltungen
- Weiterleitung interner Mail an E-Mail-Adresse schaltbar
- Verschiedene Sortierungen der Teilnehmerliste
- Anzeige von Veranstaltsungnsummern in verschiedenen Listen
- Blätternavigation in Gästebüchern
- Smiley-Verwaltung
- Semester und Ferien-Verwaltung nun mit eigenem Frontend
- Neue Berechnungsformel der Stud.IP Score ;)
- neues Sytaxhighlighting für php-Code
- weitere Detailverbesserungen und Bugfixes

# 21.3.2004 v 1.0.0-4

- Security Bug in der sendfile.php (Stefan Suchi, sendfile.php 2.33 -> 2.34)
- Falsche Ausgabe der Teilnehmerzahl bei vorläufig Akzeptierten und Kontingentierung (Tobias Thelen , details.php 2.22 -> 2.23)
- check auf leeres Passwort, wenn anonymous bind aktiviert (André Noak/Philipp Hügelmeyer lib/classes/auth_plugins/StudipAuthLdap.class.php 1.2 -> 1.3)
- Abräumen von Adresbüchern & Abräumen von internen Nachrichten (Nils Kolja Windisch, messaging.inc.php (2.24 -> 2.25)
- Statusgruppeneintrag bleib beim löschen erhalten  (Ralf Stockmann , meine_seminare.php 2.53 -> 2.54)
- Neue Versionsnummer. (Philipp Hügelmeyer, /studip-phplib/local.inc.dist VERSION)

# 07.3.2004 v 1.0.0-3

- Sicherheitsloch im Hilfesystem gefixed (Stefan Suchi studip-htdocs/help/index.php)
- Neue Versionsnummer. (Philipp Hügelmeyer, /studip-phplib/local.inc.dist VERSION)

# 4.3.2004 v 1.0.0-2

- $EXTERN_SERVER_NAME jetzt mit http:// (Peter Thienel, visual.inc.php 2.59->2.60)
- Kein anknabbern von html-Tags mehr durch externe Smileys in short notation
 (Peter Thiene, visual.inc.php 2.59->2.60)
- Bugfix: Feiertage werden bei veranstaltung_beginn berücksichtigt (Cornelis Kater, dates.inc.php 1.1 -> 1.2 (release))
- Die englische Sprachdatei war im Developer-CVS fälschlicherweise nicht als Binär-Datei eingecheckt (Stefan Suchi, studip-htdocs/locale/en/LC_MESSAGES/studip.mo 1.1 -> 1.2 (release))
- Wenn man in einem frisch installierten System in "Freie" geht, knallt es da noch, da offensichtlich die alte Literaturtabelle abgefragt wird (Andre Noack, freie.php 2.2 -> 2.3))
- neue literaturtabelle wird berücksichtigt, außerdem wiki und vote eingbaut (Cornelis Kater, functions.php 2.16->2.17)
- Ausrichtung Veranstaltungsnummer (Stefan Suchi, details.php 2.21->2.22)
- Wenn die SCM-Seite nicht betreten wird, aber Modul per deafult an, keine hässlichen leeren Reiter mehr (Cornelis Kater, admin_semiare_assi.php 2.49->2.50)
- Kommnetartext an drei Stellen besagte genau das Gegenteil von dem was Beabsichtigt ist (Cornelis Kater, local.inc.dist 1.25 -> 1.26)
- Neue Versionsnummer (Philipp Hügelmeyer, /studip-phplib/local.inc.dist VERSION)

# 29.12.2003 v 1.0.0-1

- neue WAP-Schnittstelle
- neue Literaturverwaltung zur direkten Literaturrecherche und -verwaltung aus Stud.IP heraus
- neue Funktionen zur Verwaltung systeminterner Nachrichten: Ordner, Weiterleiten an anderen Account, Löschschutz, Fallweises Bearbeiten von Signaturen, Vorschau und ein Experten-Modus zum Versenden von Nachrichten
- neue Simple-Content-Funktion zum Verwalten von beliebigem Content innerhalb von Veranstaltungen und Einrichtungen
- neuer vCard-Export für Personendaten
- neue Funktion zum Erstellen von vorformatierten Veranstaltungslisten als PDF
- neue Synchronisationsschnittstelle für Termine
- neue Gästebuchfunktion für persönliche Homepages
- Viewcounter für perönliche Homepages und News
- kompletter Download als Zip-Archiv mit Ordnerstruktur für alle Dateien oder einzelne Ordner einer Veranstaltung und Archivierung von Dateien als Zip-Archiv mit der kompletten Ordnerstruktur der Veranstaltung
- neue Formatierung: Einbindung von externen Bildern in Textbeiträge
- neue Formatierung: Umgehen aller Formatierungen
- neue Funktion zum Einbinden von grafischen Bannern auf der Startseite
- überarbeitetes Design im Terminkalender
- Veranstaltungen und Einrichtungen werden nun zusammen unter "Meine Veranstaltungen" geführt
- leichte Überarbeitung einiger Icons
- weitere Detailverbesserungen und Bugfixes
