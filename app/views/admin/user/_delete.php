<?php
/**
 * @var Admin_UserController $controller
 * @var array $users
 */
?>
<form action="<?= $controller->link_for('admin/user/delete') ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <? if ($users) : ?>
        <? $details = [] ?>
        <? foreach ($users as $user) : ?>
            <? $details[] = htmlReady(sprintf('%s (%s)', $user->getFullName(), $user->username)) ?>
            <input type="hidden" name="user_ids[]" value="<?= $user['user_id'] ?>">
        <? endforeach ?>
        <?= MessageBox::warning(_('Wollen Sie die folgenden Nutzer wirklich löschen?'), $details) ?>
    <? endif ?>


    <fieldset>
        <legend><?= _('Personenbezogene Daten') ?></legend>

        <label>
            <input type="checkbox" id="personaldocuments" name="personaldocuments"
                   value="1" checked>
            <?= _('Dokumente löschen?') ?>
            <?= tooltipHtmlIcon(_('persönlicher Dateibereich')) ?>
        </label>

        <label>
            <input type="checkbox" id="personalcontent" name="personalcontent"
                   value="1" checked>
            <?= _('Andere Inhalte löschen?') ?>
            <?= tooltipHtmlIcon(_('Inhalte der Profilseite, persöhnliche Blubber, Nachrichten')) ?>
        </label>

        <label>
            <input type="checkbox" id="personalnames" name="personalnames"
                   value="1" checked>
            <?= _('Namen löschen?') ?>
            <?= tooltipHtmlIcon(_('Vor-/ Nachname, Username, E-Mail')) ?>
        </label>

    </fieldset>

    <fieldset>
        <legend><?= _('Veranstaltungsbezogene Daten') ?></legend>

        <label>
            <input type="checkbox" id="documents" name="documents" value="1" checked>
            <?= _('Dokumente löschen?') ?>
            <?= tooltipHtmlIcon(_('Dateien in Veranstaltungen und Einrichtungen')) ?>
        </label>

        <label>
            <input type="checkbox" id="courseware" name="courseware" value="1" checked>
            <?= _('Lernmaterialien löschen?') ?>
            <?= tooltipHtmlIcon(_('Courseware Lernmaterialien, Seiten, Abschnitte und Blöcke')) ?>
        </label>

        <label>
            <input type="checkbox" id="coursecontent" name="coursecontent" value="1" checked>
            <?= _('Andere Inhalte löschen?') ?>
            <?= tooltipHtmlIcon(_('veranstaltungsbezogene Inhalte, bis auf Wiki und Forum Einträge')) ?>
        </label>

        <label>
            <input type="checkbox" id="memberships" name="memberships" value="1" checked>
            <?= _('Veranstaltungs-/Einrichtungszuordnungen löschen?') ?>
            <?= tooltipHtmlIcon(_('Zuordnungen zu Veranstaltungen, Einrichtungen, Studiengruppen')) ?>
        </label>

    </fieldset>

    <label>
        <input type="checkbox" id="mail" name="mail" value="1" checked>
        <?= _('E-Mail-Benachrichtigung verschicken?') ?>
    </label>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Ja!'), 'delete', ['title' => _('Benutzer löschen')]) ?>
        <?= Studip\Button::createCancel(_('Nein!'), 'back', ['title' => _('Abbrechen')]) ?>
    </footer>
</form>
