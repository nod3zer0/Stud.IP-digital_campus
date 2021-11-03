<form action="<?= $controller->cancel_slot($slot->block, $slot, $page) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Termin absagen') ?></legend>

        <label>
            <?= _('Termin' ) ?><br>
            <?= $this->render_partial('consultation/slot-details.php', compact('slot')) ?>
        </label>

        <label>
            <?= _('Ort') ?><br>
            <?= htmlready($slot->block->room) ?>
        </label>

    <? if (count($slot->bookings) > 1): ?>
        <div>
            <?= _('Den folgenden Personen absagen') ?><br>
            <ul class="list-unstyled">
            <? foreach ($slot->bookings as $booking): ?>
                <li>
                    <label class="undecorated">
                        <input type="checkbox" name="ids[]" checked
                               value="<?= htmlReady($booking->id) ?>">
                        <?= htmlReady($booking->user->getFullName()) ?>
                    </label>
                </li>
            <? endforeach; ?>
            </ul>
        </div>
        <br>
    <? endif; ?>

        <label>
            <?= _('Grund der Absage') ?>
            <textarea name="reason"></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Termin absagen')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->indexURL($page, "#block-{$slot->block_id}")
        ) ?>
    </footer>
</form>
