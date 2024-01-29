<?php
/**
 * @var Calendar_CalendarController $controller
 */
?>
<form class="default"
      method="post"
      data-dialog="size=auto"
      enctype="multipart/form-data"
      action="<?= $controller->link_for('calendar/calendar/import_file/') ?>">
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= sprintf(_('Termine importieren')) ?>
        </legend>
        <label for="event-type">
            <input type="checkbox" name="import_privat" value="1" checked>
            <?= _('Öffentliche Termine als "privat" importieren') ?>
        </label>
        <label>
            <span class="required"><?= _('Datei zum Importieren wählen') ?></span>
            <input required type="file" name="importfile" accept=".ics,.ifb,.iCal,.iFBf">
        </label>
    </fieldset>
    <footer data-dialog-button>
        <?= \Studip\Button::create(_('Importieren'), 'import') ?>
        <?= \Studip\Button::createCancel(_('Abbrechen')) ?>
    </footer>
</form>
