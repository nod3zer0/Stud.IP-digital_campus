<? if ($selected_room): ?>
    <label>
        <?= _('Ausgewählter Raum') ?>
        <input type="hidden" name="selected_room_id"
               value="<?= htmlReady($selected_room->id) ?>">
        <br>

        <strong><?= htmlReady($selected_room->name) ?></strong>
    </label>
<? endif ?>
