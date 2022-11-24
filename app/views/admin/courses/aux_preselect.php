<?php
/**
 * @var array $values
 * @var AuxLockRule[] $aux_lock_rules
 */
?>
<label><?= _('Für alle Veranstaltungen') ?>
    <select name="lock_sem_all" style="max-width: 200px">
        <option value="none">
            --<?= _('keine Zusatzangaben') ?>--
        </option>
    <? foreach ($aux_lock_rules as $rule) : ?>
        <option value="<?= htmlReady($rule->id) ?>"
            <? if ($values['aux_lock_rule'] === $rule->id) echo 'selected'; ?>>
            <?= htmlReady($rule->name) ?>
        </option>
    <? endforeach ?>
    </select>
</label>
<label>
    <input type="checkbox" value="1" name="aux_all_forced">
    <?=_('Erzwungen')?>
</label>
<?= \Studip\Button::createAccept(_('Speichern'), 'all'); ?>
