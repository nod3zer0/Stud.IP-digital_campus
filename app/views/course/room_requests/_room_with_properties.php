<label><?= _('Ausgewählter Raum') ?></label>
<? if ($selected_room): ?>
    <input type="hidden" name="selected_room_id"
           value="<?= htmlReady($selected_room->id) ?>">
    <?= htmlReady($selected_room->name) ?>
<? endif ?>

<?= var_dump($selected_room); ?>
