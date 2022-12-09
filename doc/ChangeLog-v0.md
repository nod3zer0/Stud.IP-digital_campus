# 24.12.2003 v 0.9.5-4 (Weihnachtsrelease)

- Neue Funktion insert_seminar_user. (Cornelis Kater)
- Bugfix: Entfernt die Schleife Passwortabfrage <-> vorläufig eintragen (Till Glöggler).
- Bugfix:  teilnehmer.php:  Fehlende $mkdate -Initialisierung (Till Glöggler).
- Bugfixes: admin_seminare_assi.php: Stunden und Minuten bei der Kontrolle verdreht.
- Statt dem aktuellen Datum wird ein Platzhalter eingefügt, was dazu führt, dass Veranstaltungen ohne explizites Startdatum den Wert -1 in der Tabelle stehen haben. (Till Glöggler)
- Sicherheitslücke in der phplib (Stefan Suchi)
- Die Unterordner pictures/tex/ und user/ sind wieder im Release (Philipp Hügelmeyer)
- dvips-Aufruf gefixed, so dass er auch unter Redhat und Fedora tut. (Philipp Hügelmeyer)
- Neue Versionsnummer (Philipp Hügelmeyer)

# 07.12.2003 v 0.9.5-3

- Zwei Bugs im Wiki (Falscher Rechtecheck und Bearbeiten Link) (Tobias Thelen)
- Tabellendarstellung in show_admission.php unter ns4.7 gefixed (Till Glöggler)
- Neue Versionsnummer (Philipp Hügelmeyer)

# 12.11.2003 v 0.9.5-2

- Behebung zweier Fehler in den Konvertierungsskripten von 0.9.0 nach 0.9.5 (Jens Schmelzer)
- Fehler beim Anlegen von Ordnern behoben (Cornelis Kater )
- erster Wiederholungstermin bei jaehricher Wiederholung wird gezaehlt (Peter Thienel)
- korrekte Verwendung der Variable $TMP_PATH (Jens Schmelzer, Philipp Hügelmeyer)
- Neue Versionsnummer (Philipp Hügelmeyer)

# 30.09.2003 v 0.9.5

- neues Modul für Votings und Tests
- neues Wiki-Web-Modul zum kollaborativen Erstellen von verlinkten Dokumenten
- komplette Überarbeitung der Engine für Forensysteme, um neue Funktionen erweitert (Flatview, Indikatoren, Sortierung, Favoriten, AutoShrink)
- komplette Überarbeitung des Bereichs zum Versenden von systeminternen-Nachrichten: Mehrere Empfänger können ausgewählt werden, gesendete und empfangene Nachrichten, bessere Bedienung
- StartUp-Checker: Überprüft vor dem Anlegen von Veranstaltungen, ob alle Voraussetzungen zum Anlegen gegebenen sind und bietet gegebenenfalls Hilfestellungen.
- Erweiterungen am Dateisystem: Angabe eines freien Namens für Dateien, Möglichkeit zur Aktualisierung bestehenden Dateien
- Erweiterungen am Anmeldesystem: Einstellen des Anmeldezeitraumes, Verfahren um Nutzer vorläufig in Veranstaltungen aufzunehmen.
- neues Verfahren zur Erweiterung der Metadaten von Veranstaltungen, Einrichtungen und Nutzer um generische Felder
- Modularisierung von Veranstaltungen und Einrichtungen: Alle Bereiche können jederzeit für einzelne Veranstaltungen und Einrichtungen an- bzw. abgeschaltet werden.
- externe Authentifizierungen (LDAP, Radius, andere Stud.IP Installationen) sind über eine Plug-In- Architektur möglich
- Internationalisierung: Die Internationalisierung und Erstellung einer englischen Sprachversion ist komplett abgeschlossen.
- weitere Detailverbesserungen und Bugfixes

# 31.03.2003 v 0.9

- erweitertes Chatsystem: eigene Chaträume für Veranstaltungen, Personen und Einrichtungen, Protokoll- Funktion, Chaträume können mit einem Passwort gesichert werden
- Verknüpfungsmöglichkeit mit Lernmodulen von ILIAS Open Source (muss ebenfalls installiert werden)
- systemweiter TeX-Support
- personalisierte Übersicht der persönlichen Veranstaltungen im Archiv
- automatische Verwaltung und Konfiguration für externe Seiten (zur Einbindung des Stud.IP Contents in bestehende Internetseiten)
- Veranstaltungssuche / Browser umgebaut und erweitert, verwendet nun zur Darstellung die neuen Bäume
- Internationalisierung: weite Teile des Systems können nun auch in Englisch benutzt werden
- Ressourcenverwaltung wird nun für Verwaltung der Räume von Veranstaltungen genutzt (falls aktiviert)
- neue Ressourcenverwaltung (eigenständiges Modul) zur Verwaltung von Räumen, Gebäuden, Geräten und weiteren Ressourcen.
- neues Adressbuch auf Grundlage der Buddyverwaltung
- neues Exporttool, unterstützt Ausgaben in XML, RTF, TXT und PDF
- neue Einrichtungssuche, verwendet den Einrichtungsbaum
- neuer Einrichtungsbaum zur Darstellung der Uni-Hierachie
- neuer Vorlesungsbaum zur Darstellung von Veranstaltungshierachien (etwa Vorlesungsverzeichnis)
- bei regelmäßigen Veranstaltungen kann nun ein Raum für jeden Termin angegeben werden
- Ablaufplanverwaltung überarbeitet
- neue Titelverwaltung für Nutzernamen mit vor- und nachgestellten Titeln
- überarbeitete Startseite
- verbesserte Konfigurierbarkeit des Mailversands
- weitere Detailverbesserungen und Bugfixes

# 13.10.2002 v 0.8.15 ;-)

- Neue Übersicht der persönlichen Termine auf der Startseite
- Terminkalender überarbeitet
- Kürzel für Symbole wie Pfeile oder Copyright-Zeichen eingebaut
- neue Verwaltungsebene "Funktionen / Gruppen", verwendbar für Einrichtungen und Veranstaltungen
- grafische Aktivitätsanzeige in Veranstaltungen kann vom Dozenten aktiviert werden
- Anpassungen für die Funktionalität von Veranstaltungen als "Work Groups"
- weitere Detailverbesserungen und Bugfixes

# 31.08.2002 v 0.8 release candidate 3

- Verschieben von Postings in andere Einrichtungen nun möglich
- neues Wartungstool um die Integrität der Datenbank zu testen und wiederherzustellen
- beliebige Teilnehmer können nun von Berechtigten direkt berufen werden
- neues Hovern von Tabellen eingebaut
- Umstrukturierung der Reiter im Adminbereich in logische Einheiten
- neue Übersichtsseite "Meine Einrichtungen" für direkten Zugriff auf Einrichtungsobjekte eingebaut.
- Äusserst umfangreiche und flexible Funktionen zur Teilnehmerbegrenzung von Veranstaltungen. Veranstaltungen können  mit einem eigenen Losverfahren oder nach chronologischer Anmeldung mit unterschiedlichen Kontingenten für beliebig viele Studiengänge begrenzt werden
- neue Ebene "Studienfach" eingebaut. Studenten können nun im System hinterlegen, welche Studienfächer sie studieren
- neuer Archivierungsassistent
- System zur optischen Gewichtung des Alters von Objekten eingeführt (Pfeile sind je nach alter mehr oder weniger rot)
- Veranstaltungssuche/Übersicht verbessert: optische besser hervorgehobene Gliederung, frei wählbare Gliederungsmodi, verbesserte und übersichtlichere Darstellung
- Rundmails an alle Buddies können verschickt werden
- neue Einrichtungssuche eingebaut
- Selbstbezeichnung für Einrichtungen kann nun verändert werden
- Einrichtungen können nun als eigenes Objekt betrachtet werden: Damit gibt es eigene Foren, Dateibereich, Mitarbeiterlisten und Veranstaltungsübersicht an jedem Institut innerhalb des Systems
- leichte Veränderung des Designs, neues Hintergrundbild
- Geschwindigkeitsoptimierungen
- weitere Detailverbesserungen und Bugfixes

 10.03.2002 v 0.7.5 release candidate 2

- bessere Performance der Veranstaltungsübersicht
- Druckansicht für ein komplettes Forum einer Veranstaltung
- beim Archivieren wird ein Forumdump mit archiviert
- der Terminkalender wurde weiter verbessert, insbesondere das Handling von Ganztagsterminen
- Zitierfunktion im Forum und SMS eingefügt
- v 2.0 des Chats fertig gestellt: neue Funktionen: SMS aus dem Chat, private Nachrichten, alle Schnellformatierungen verfügbar, Performance optimiert
- Druckansicht für das Stundenplanmodul eingefügt
- umfangreiche Schnellformatierungen eingeführt. Dafür wird aus Sicherheitsgründen kein html-Code mehr akzeptiert
- Linkerkennung verbessert, Formatierungstag für Linknamen eingeführt
- Smiley-Funktion überarbeitet: Neue Smileys, Smiley-Tag wird auf Alt-Tag eingeblendet, Smiley-Übersichtsseite als Link auf jedes Smiley
- v 2.0 des SMS-Systems: Nachrichten werden nicht mehr sofort gelöscht, automatisches Löschen beim Logout auf Wunsch, Signatur für SMS
- Messsenger verbessert
- Buddylisten eingefüngt
- neue Konfigurationsseite Messaging: Buddieverwaltung, Funktion zum automatischen Starten des Messengers, Einstellungen für SMS-System
- Hilfeseiten auf den aktuellen Stand gebracht
- mehr Smileys :)
- weitere Detailverbesserungen und Bugfixes

# 13.01.2002 v 0.7.0 release candidate 1

- Komplett neu geschriebener Terminkalender: Tages-, Monats-, Wochen- und Jahresansicht, flexible Wiederholungstermine, farbliche Kennungen für Termine, freie Einbindung vom Veranstaltungsterminen, PIM-Optik, zahlreiche Optionen über MyStudip-Bereich frei konfigurierbar
- verbesserte Ablaufplanverwaltung: Optik an Fluid-Design angepasst, nachträgliches Anlegen von Datei und Forenordnern nun möglich, verbesserte Bedienung
- Veranstaltungsübersicht für Instituts-Admins durch Stundenplanmodul realisiert
- Semesterauswahl für Stundenplan nun möglich
- MyStud.IP Bereich als eingeständiges Modul erstellt und ausgebaut: Optionen für Forum, Stundenplan und Terminkalender können hier zentral verwaltet werden.
- Neuer Autologin, der html-Datei als Login Script an den Benutzer verschickt
- v 3.0 der Newsverwaltung: Übersichtliches Auswählen von gewünschten Bereichen, verbesserte Bedienung, zahlreiche Detailverbesserungen
- Aufteilung aller Veranstaltung in beliebig viele Kategorien, etwa Kultur und Lehre
- TOP Listen bei der Veranstaltungsauswahl zeigen meistbesuchte, zuletzt angelegte, aktivste und teilnehmerstärkste Veranstaltungen an
- Veranstaltungsauswahl über Reiter in Bereiche strukturiert
- Persönliche Seite in der Bedienung verbessert: Einzelne Bereiche können direkt angesprungen werden
- v 2.0 der Verwaltung eigener Kategorien
- Optik der Startseite verbessert und differenzierte Startseiten für verschiedene Nutzergruppen realisiert
- Adminbereich neustrukturiert: Auswahllisten für einzelne Bereiche sind identisch im Aufbau, Veranstaltungen können vorgewählt werden, so dass das Verwalten mehrerer Aspekte einer Veranstaltung leicht fällt
- Optik des Archivs verbessert, es kann auf selbst besuchte Veranstaltungen eingegrenzt werden
- Der Download wurde verbessert: Download ist als ZIP-Archiv für alle Dateitypen möglich, MIME-Typen werden für die wichtigsten Dateien erkannt
- Aussagekräftigere Fehlermeldungen in vielen Bereichen umgesetzt
- Geschlecht kann bei der Registrierung mit angegeben werden, Zeitpunkt der Erstanmeldung wird gespeichert
- Übersichtliche Kennzeichnung von Veränderungen in besuchten Seminaren auf der Übersichtsseite
- Verschieben ist im Forum möglich, etliche Detailverbesserungen im Forenbereich
- Impressum neu strukturiert
- Optik des Literaturbereichs verbessert
- Handling von Sonderzeichen systemweit optimiert
- weitere Detailverbesserungen und Bugfixes

# 01.10.2001 v 0.6.0 beta 4

- erste komplett überarbeitete und in vielen Details neu durchdachte und strukturierte Version
- v.2.0 des Forensystems (NT Version): Komplett graphischer Treeview, JavaScript Effekte zur schnellen Verarbeitung der Informationen
- Assistent zum Anlegen von Seminaren
- Neues Terminsystem, das flexible Verwaltung von Terminmetadaten ermöglicht
- Neue Suchfunktion in der globalen Nutzerverwaltung
- v.2.0 der Terminverwaltung und des Assistenten zum Erstellen eines Ablaufplans
- v.3.0 der Ordnerverwaltung: rekursive Ordnerverwaltung, flexibles Anpassen an unterschiedliche Anforderungen, Download per send-Methode
- Optimierung der Dateiverwaltung durch das OS
- Optimierung des Zusammenspiels zwischen Terminen und Dokumenten/Ordnern
- deutlich verbesserte Archivsuche
- nachträgliches Vergeben von Berechtigungen im Archiv nun möglich
- Anzeigen der berechtigten Personen im Archiv möglich
- Scorefunktion eingefügt (Codename geschnitten Brot)
- freies Verwalten von Kategorien auf der persönlichen Homepage (Codename Extrawurst) eingefügt
- mystudip eingefügt
- Stud.IP Messenger eingefügt (beta)
- Fluid 'n' table_header Optik eingeführt
- Ersetzen von Buttons durch Grafikbuttons begonnen
- Detailverbesserungen am Seminar-Browser
- Neue Reiteroptik
- weitere Detailverbesserungen und Bugfixes

# 07.06.2001 v 0.5.0 beta 3

- Release bei Sourceforge
- weitere Fixes
- Graphische Ausgabe des Stundenplans
- SMS nicht mehr auf 255 Zeichen begrenzt

# 01.05,2001 v 0.4.0 beta 2

- Archiv eingefügt
- neuer Seminar-Browser (Suchfunktion für Veranstaltungen)
- automatisches Archivieren der Veranstaltungen
- Bereichs- und Fachverwaltung
- v.2.0 der Dateiverwaltung: Upload nun über grafische Oberfläche, orientiert am Explorerlook
- kontextsensitives Hilfesystem
- Gruppierung von "Meine Veranstaltungen" über Farbcodes
- v.2.0 des Newssystems
- Facelifting beim Design: neue Buttons, konsequenter Fensterlook

# 19.03.2001 v 0.3.0 beta 1

- erster Release bei Sourceforge
- unzählige Buxfixes
- viele Detail-Verbesserungen, die sich im laufenden Betrieb ergeben haben
- diese Version hat den Betrieb über ein Semester als Produktivsystem am Zentrum für interdisziplinäre Medienwissenschaft ohne Probleme überstanden

# 16.10.2000 v 0.2.0 alpha

- In letzter Sekunde wird pünktlich zum Start des WS 2000/01 die erste Version im öffentlichen Einsatz fertig gestellt
- Neue Funktionen: Dateiordner, Chat, SMS-System, Anzeige von Personen, die Online sind,   Terminplaner, Personensuche und persönliche Homepage
- Erste Version mit durchgängiger CI
- Navigation per Reitersystem
- Anbindung an externe Seiten
- Das Chatmodul wird eingefügt

# 11.01.2000 v 0.1.0

- Die erste Version im Lehreinsatz: Die Veranstaltung "Kurzfilmpraxis" von Ralf Stockmann nutzt das personalisierte Forum zur Entwicklung von Drehbüchern
