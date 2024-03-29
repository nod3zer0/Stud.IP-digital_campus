<?php
/**
 * @var array $values
 * @var SimpleCollection $all_lock_rules
 * @var Course $course
 */
?>
<? $current_lock_rule = $all_lock_rules->findOneBy('lock_id', $course->lock_rule); ?>
<? if (!$GLOBALS['perm']->have_perm('root') && ($current_lock_rule['permission'] == 'admin' || $current_lock_rule['permission'] == 'root')) : ?>
    <?= htmlReady($current_lock_rule['name'])?>
<? else : ?>
    <select name="lock_sem[<?= htmlReady($course->id) ?>]" style="max-width: 200px">
    <? foreach ($all_lock_rules as $lock_rule): ?>
        <option value="<?= $lock_rule['lock_id'] ?>" <?= $lock_rule['lock_id'] === $course->lock_rule ?  'selected' : '' ?>>
            <?= htmlReady($lock_rule['name']) ?>
        </option>
    <? endforeach; ?>
    </select>
<? endif ?>
