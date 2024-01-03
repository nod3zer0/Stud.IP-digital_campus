<?php

class Tic3384AddMvvLogActions extends Migration
{

    public function description()
    {
        return 'Adds new log actions to mvv for contacts and files.';
    }

    public function up() {
        StudipLog::registerAction(
            'MVV_CONTACT_NEW',
            'MVV: Kontaktperson erstellen',
            '%user erstellt neue Kontaktperson %contact(%affected).',
            'MVV'
        );
        StudipLog::registerAction(
            'MVV_CONTACT_UPDATE',
            'MVV: Kontaktperson ändern',
            '%user ändert Kontaktperson %contact(%affected).',
            'MVV'
        );
        StudipLog::registerAction(
            'MVV_CONTACT_DELETE',
            'MVV: Kontaktperson löschen',
            '%user löscht Kontaktperson %contact(%affected).',
            'MVV'
        );

        StudipLog::registerAction(
            'MVV_EXTERN_CONTACT_NEW',
            'MVV: Externe Kontaktperson erstellen',
            '%user erstellt neue externe Kontaktperson %contact(%affected).',
            'MVV'
        );

        StudipLog::registerAction(
            'MVV_EXTERN_CONTACT_UPDATE',
            'MVV: Externe Kontaktperson ändern',
            '%user ändert externe Kontaktperson %contact(%affected).',
            'MVV'
        );
        StudipLog::registerAction(
            'MVV_EXTERN_CONTACT_DELETE',
            'MVV: Externe Kontaktperson löschen',
            '%user löscht externe Kontaktperson %contact(%affected).',
            'MVV'
        );

        StudipLog::registerAction(
            'MVV_CONTACT_RANGE_NEW',
            'MVV: Kontaktperson zuordnen',
            '%user ordnet die Kontaktperson %contact(%affected) dem Bereich %range(%coaffected) zu.',
            'MVV'
        );
        StudipLog::registerAction(
            'MVV_CONTACT_RANGE_DELETE',
            'MVV: Zuordnung der Kontaktperson löschen',
            '%user löscht die Zuordnung der Kontaktperson %contact(%affected) zum Bereich %range(%coaffected).',
            'MVV'
        );
        StudipLog::registerAction(
            'MVV_CONTACT_RANGE_UPDATE',
            'MVV: Zuordnung der Kontaktperson ändern',
            '%user ändert die Zuordnung der Kontaktperson %contact(%affected) zum Bereich %range(%coaffected).',
            'MVV'
        );

        StudipLog::registerAction(
            'MVV_FILE_NEW',
            'MVV: Material/Dokument erstellen',
            '%user erstellt neues Material/Dokument %file(%affected).',
            'MVV'
        );
        StudipLog::registerAction(
            'MVV_FILE_UPDATE',
            'MVV: Material/Dokument ändern',
            '%user ändert Material/Dokument %file(%affected).',
            'MVV'
        );
        StudipLog::registerAction(
            'MVV_FILE_DELETE',
            'MVV: Material/Dokument löschen',
            '%user löscht Material/Dokument %file(%affected).',
            'MVV'
        );

        StudipLog::registerAction(
            'MVV_FILE_FILEREF_NEW',
            'MVV: Datei erstellen',
            '%user erstellt neue Datei %fileref(%affected).',
            'MVV'
        );
        StudipLog::registerAction(
            'MVV_FILE_FILEREF_UPDATE',
            'MVV: Datei ändern',
            '%user ändert Datei %fileref(%affected).',
            'MVV'
        );
        StudipLog::registerAction(
            'MVV_FILE_FILEREF_DELETE',
            'MVV: Datei löschen',
            '%user löscht Datei %fileref(%affected).',
            'MVV'
        );

        StudipLog::registerAction(
            'MVV_FILE_RANGE_NEW',
            'MVV: Material/Dokument zuordnen',
            '%user ordnet Material/Dokument %fileref(%affected) zum Bereich %range(%coaffected) zu.',
            'MVV'
        );
        StudipLog::registerAction(
            'MVV_FILE_RANGE_UPDATE',
            'MVV: Zuordnung von Material/Dokument zu Bereich ändern.',
            '%user ändert Zuordnung von Material/Dokument %fileref(%affected) zu Bereich %range(%coaffected).',
            'MVV'
        );
        StudipLog::registerAction(
            'MVV_FILE_RANGE_DELETE',
            'MVV: Zuordnung von Material/Dokument zu Bereich löschen',
            '%user löscht Zuordnung von Material/Dokument %fileref(%affected) von Bereich %range/%coaffected).',
            'MVV'
        );
    }

    public function down() {
        StudipLog::unregisterAction('MVV_CONTACT_NEW');
        StudipLog::unregisterAction('MVV_CONTACT_UPDATE');
        StudipLog::unregisterAction('MVV_CONTACT_DELETE');

        StudipLog::unregisterAction('MVV_EXTERN_CONTACT_NEW');
        StudipLog::unregisterAction('MVV_EXTERN_CONTACT_UPDATE');
        StudipLog::unregisterAction('MVV_EXTERN_CONTACT_DELETE');

        StudipLog::unregisterAction('MVV_CONTACT_RANGE_NEW');
        StudipLog::unregisterAction('MVV_CONTACT_RANGE_UPDATE');
        StudipLog::unregisterAction('MVV_CONTACT_RANGE_DELETE');

        StudipLog::unregisterAction('MVV_FILE_NEW');
        StudipLog::unregisterAction('MVV_FILE_UPDATE');
        StudipLog::unregisterAction('MVV_FILE_DELETE');

        StudipLog::unregisterAction('MVV_FILE_FILEREF_NEW');
        StudipLog::unregisterAction('MVV_FILE_FILEREF_UPDATE');
        StudipLog::unregisterAction('MVV_FILE_FILEREF_DELETE');

        StudipLog::unregisterAction('MVV_FILE_RANGE_NEW');
        StudipLog::unregisterAction('MVV_FILE_RANGE_UPDATE');
        StudipLog::unregisterAction('MVV_FILE_RANGE_DELETE');
    }

}
