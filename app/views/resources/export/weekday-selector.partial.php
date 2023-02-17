<?php
/**
 * @var string[] $weekdays
 */

$days_of_the_week = [
    '1' => _('Montag'),
    '2' => _('Dienstag'),
    '3' => _('Mittwoch'),
    '4' => _('Donnerstag'),
    '5' => _('Freitag'),
    '6' => _('Samstag'),
    '7' => _('Sonntag'),
]
?>
<fieldset>
    <legend><?= _('Wochentage auswählen') ?></legend>
    <div class="hgroup">
    <? foreach ($days_of_the_week as $index => $label): ?>
        <label>
            <input type="checkbox" name="weekdays[]" value="<?= $index ?>"
                <?= in_array($index, $weekdays) ? 'checked' : '' ?>>
            <?= $label ?>
        </label>
    <? endforeach; ?>
    </div>
</fieldset>
