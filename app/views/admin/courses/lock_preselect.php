<?php
/**
 * @var Course $course
 * @var SimpleCollection $all_lock_rules
 */
?>
<label><?= _('Für alle Veranstaltungen') ?>
    <select name="lock_sem_all" style="max-width: 200px">
        <? for ($i = 0; $i < count($all_lock_rules); $i++) : ?>
            <option value="<?= $all_lock_rules[$i]["lock_id"] ?>"
                <?= $all_lock_rules[$i]['lock_id'] === $course->lock_rule ? 'selected' : '' ?>>
                <?= htmlReady($all_lock_rules[$i]["name"]) ?>
            </option>
        <? endfor ?>
    </select>
</label>

<?= \Studip\Button::createAccept(_('Zuweisen'), 'all', ['formaction' => URLHelper::getURL('dispatch.php/admin/courses/set_lockrule')]); ?>
