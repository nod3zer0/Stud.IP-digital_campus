Seit der Version 1.6 gibt es einen Migrationsmechanismus für die Stud.IP Datenbank.
Eingesetzt werden kann dieser ab einem Wechsel von Stud.IP 1.5.0-2 zu 1.6.0-1:
- **Datensicherungen** durchführen!
- **ACHTUNG**! Der Migrationsmechanismus ist für Sprünge über mehrere major releases nicht getestet! 
- Zumindest für einen Sprung von 1.5.x auf 1.7.x oder neuer sind Probleme bekannt.
- die alten Script-Dateien durch die neuen ersetzen, dabei besonders auf die Dateien im Verzeichnis config sowie auf eventuelle eigene Anpassungen achten.
- Datenbankaktualisierungen entweder über die Webschnittstelle: `http://mein.server.de/studip/web_migrate.php`
- ODER über die Komandozeile: `studip-4.6/cli/migrate.php` vornehmen.

- **ACHTUNG**! Bei einem Upgrade von Version 3.x auf Version 4.x muss die alte Konfigurationsdatei `config_local.inc.php` noch im Konfigurationsverzeichnis liegen
  während die Migrationen ausgeführt werden.

The StudIP Core Group <[info@studip.de](mailto:info@studip.de)> 2022
