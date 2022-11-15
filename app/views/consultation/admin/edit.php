<?php
/**
 * @var Consultation_AdminController $controller
 * @var ConsultationBlock $block
 * @var int $page
 * @var array|null $responsible
 */
?>
<form action="<?= $controller->store_edited($block, $page) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <?= MessageBox::info(
        _('Das Ändern der Informationen wird auch alle Termine dieses Blocks ändern.')
    )->hideClose() ?>

    <fieldset>
        <legend><?= _('Terminblock bearbeiten') ?></legend>

        <label>
            <span class="required"><?= _('Ort') ?></span>
            <input required type="text" name="room" placeholder="<?= _('Ort') ?>"
                   value="<?= htmlReady($block->room) ?>">
        </label>

        <label>
            <?=_('Information zu den Terminen in diesem Block') ?> (<?= _('Öffentlich einsehbar') ?>)
            <textarea name="note"><?= htmlReady($block->note ) ?></textarea>
        </label>

        <label>
            <input type="checkbox" name="lock" value="1" data-shows=".lock-inputs" data-activates=".lock-inputs input"
                   <? if ($block->lock_time) echo 'checked'; ?>>
            <?= _('Termine für Buchungen sperren?') ?>
        </label>

        <label class="lock-inputs">
            <?= _('Wieviele Stunden vor Beginn des Blocks sollen die Termine für Buchungen gesperrt werden?') ?>
            <input type="number" name="lock_time"
                   value="<?= htmlReady($block->lock_time) ?>"
                   min="1">
        </label>

        <? if ($responsible): ?>
        <?= $this->render_partial('consultation/admin/block-responsibilities.php', compact('responsible', 'block')) ?>
    <? endif; ?>

        <label>
            <?= _('Maximale Teilnehmerzahl') ?>
            <?= tooltipIcon(_('Falls Sie mehrere Personen zulassen wollen (wie z.B. zu einer Klausureinsicht), so geben Sie hier die maximale Anzahl an Personen an, die sich anmelden dürfen.')) ?>
            <input required type="text" name="size" id="size"
                   min="1" max="50" value="<?= $block->size ?>">
        </label>

        <label>
            <input type="checkbox" name="calender-events" value="1"
                <? if ($block->calendar_events) echo 'checked'; ?>>
            <?= _('Die freien Termine auch im Kalender markieren') ?>
        </label>

        <label>
            <input type="checkbox" name="show-participants" value="1"
                <? if ($block->show_participants) echo 'checked'; ?>>
            <?= _('Namen der buchenden Personen sind öffentlich sichtbar') ?>
        </label>

        <label>
            <?= _('Grund der Buchung abfragen') ?>
        </label>
        <div class="hgroup">
            <label>
                <input type="radio" name="require-reason" value="yes"
                    <? if ($block->require_reason === 'yes') echo 'checked'; ?>>
                <?= _('Ja, zwingend erforderlich') ?>
            </label>

            <label>
                <input type="radio" name="require-reason" value="optional"
                    <? if ($block->require_reason === 'optional') echo 'checked'; ?>>
                <?= _('Ja, optional') ?>
            </label>

            <label>
                <input type="radio" name="require-reason" value="no"
                    <? if ($block->require_reason === 'no') echo 'checked'; ?>>
                <?= _('Nein') ?>
            </label>
        </div>

        <label>
            <?= _('Bestätigung für folgenden Text einholen') ?>
            (<?= _('optional') ?>)
            <?= tooltipIcon(_('Wird hier ein Text eingegeben, so müssen Buchende bestätigen, dass sie diesen Text gelesen haben.')) ?>
            <textarea name="confirmation-text"><?= htmlReady($block->confirmation_text) ?></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->indexURL($page)
        ) ?>
    </footer>
</form>
