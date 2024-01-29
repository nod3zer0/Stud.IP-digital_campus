<?php
/**
 * @var Calendar_CalendarController $controller
 * @var string $user_id
 * @var string $dates_to_export
 * @var DateTimeImmutable $begin
 * @var DateTimeImmutable $end
 */
?>
<form class="default" method="post"
      action="<?= $controller->link_for('calendar/calendar/export/' . $user_id) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Termine exportieren') ?></legend>
        <label>
            <?= _('Zu exportierende Termine') ?>
            <select name="dates_to_export">
                <option value="user"
                    <?= $dates_to_export === 'user' ? 'selected' : '' ?>>
                    <?= _('Persönliche Termine') ?>
                </option>
                <option value="course"
                    <?= $dates_to_export === 'course' ? 'selected' : '' ?>>
                    <?= _('Veranstaltungstermine') ?>
                </option>
                <option value="all"
                    <?= $dates_to_export === 'all' ? 'selected' : '' ?>>
                    <?= _('Alle Termine') ?>
                </option>
            </select>
        </label>
        <label>
            <?= _('Startdatum') ?>
            <input type="text" value="<?= htmlReady($begin->format('d.m.Y')) ?>" name="begin" data-date-picker>
        </label>
        <label>
            <?= _('Enddatum') ?>
            <input type="text" value="<?= htmlReady($end->format('d.m.Y')) ?>" name="end" data-date-picker>
        </label>
    </fieldset>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Exportieren'), 'export') ?>
        <?= \Studip\Button::createCancel(_('Abbrechen')) ?>
    </div>
</form>
