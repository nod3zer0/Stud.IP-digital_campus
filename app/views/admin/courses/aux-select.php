<?php
/**
 * @var Course $course
 * @var array $aux_lock_rules
 */
?>
<select name="lock_sem[<?= htmlReady($course->id) ?>]" style="max-width: 200px">
<? foreach ($aux_lock_rules as $id => $rule) : ?>
    <option value="<?= $id ?>" <?= $values['aux_lock_rule'] == $id ?  'selected' : '' ?>>
        <?= htmlReady($rule['name']) ?>
    </option>
<? endforeach ?>
</select>
<br>
<label>
    <input type="checkbox" value="1" name="lock_sem_forced[<?= htmlReady($course->id) ?>]"
           <?= $values['aux_lock_rule_forced'] ? 'checked' : '' ?>>
    <?=_('Erzwungen')?>
</label>
