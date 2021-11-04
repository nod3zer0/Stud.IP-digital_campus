<?php
$block = $block ?? false;
$selected = function ($type, $id) use ($block) {
    if (!$block ) {
        return '';
    }
    $matched = $block->responsibilities->filter(function ($responsibility) use ($type, $id) {
        return $responsibility->range_type === $type && $responsibility->range_id === $id;
    });
    return count($matched) > 0 ? 'selected' : '';
}
?>
<? if (!empty($responsible['users'])): ?>
    <label>
        <?= _('Durchführende Person(en)') ?>
        <select name="responsibilities[user][]" multiple class="nested-select">
            <? foreach ($responsible['users'] as $user): ?>
                <option value="<?= htmlReady($user->id) ?>" <?= $selected('user', $user->id) ?>>
                    <?= htmlReady($user->getFullName()) ?>
                </option>
            <? endforeach; ?>
        </select>
    </label>
<? endif; ?>

<? if (!empty($responsible['groups'])): ?>
    <label>
        <?= _('Durchführende Gruppe(n)') ?>
        <select name="responsibilities[statusgroup][]" multiple class="nested-select">
            <? foreach ($responsible['groups'] as $group): ?>
                <option value="<?= htmlReady($group->id) ?>" <?= $selected('statusgroup', $group->id) ?>>
                    <?= htmlReady($group->getName()) ?>
                </option>
            <? endforeach; ?>
        </select>
    </label>
<? endif; ?>

<? if (!empty($responsible['institutes'])): ?>
    <label>
        <?= _('Durchführende Einrichtung(en)') ?>
        <select name="responsibilities[institute][]" multiple class="nested-select">
            <? foreach ($responsible['institutes'] as $institute): ?>
                <option value="<?= htmlReady($institute->id) ?>" <?= $selected('institute', $institute->id) ?>>
                    <?= htmlReady($institute->getFullname()) ?>
                </option>
            <? endforeach; ?>
        </select>
    </label>
<? endif; ?>
