<?php
/**
 * @var Course $course
 * @var AuxLockRule[] $aux_lock_rules
 * @var array $values
 */
?>
<select name="lock_sem[<?= htmlReady($course->id) ?>]" style="max-width: 200px">
    <option value="none">
        --<?= _('keine Zusatzangaben') ?>--
    </option>
<? foreach ($aux_lock_rules as $rule) : ?>
    <option value="<?= htmlReady($rule->id) ?>" <? if ($values['aux_lock_rule'] === $rule->id) echo 'selected'; ?>>
        <?= htmlReady($rule->name) ?>
    </option>
<? endforeach ?>
</select>
<br>
<label>
    <input type="checkbox" value="1" name="lock_sem_forced[<?= htmlReady($course->id) ?>]"
           <?= $values['aux_lock_rule_forced'] ? 'checked' : '' ?>>
    <?=_('Erzwungen')?>
</label>
