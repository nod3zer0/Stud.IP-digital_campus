<?php
# Lifter010: TODO
$is_locked = $input['locked'] ? 'disabled readonly' : '';
$is_locked_array = $input['locked'] ? ['disabled' => true, 'readonly' => true] : [];
$is_required_array = !empty($input['must']) ? ['required' => true] : [];
$is_pattern_array = !empty($input['pattern']) ? ['pattern' => $input['pattern']] : [];
if ($input['type'] === "text") : ?>
    <? if (!empty($input['i18n'])) : ?>
        <?= I18N::input($input['name'], $input['value'], $is_locked_array + $is_required_array + $is_pattern_array) ?>
    <? else : ?>
        <input <?=$is_locked ?> type="text" name="<?= htmlReady($input['name']) ?>" value="<?= htmlReady($input['value']) ?>" <? if (!empty($input['must'])) echo 'required'; ?> <? if (!empty($input['pattern'])) printf('pattern="%s"', htmlReady($input['pattern'])) ?>>
    <? endif ?>
<? endif;

if ($input['type'] === "number") : ?>
    <input <?=$is_locked ?> type="number" name="<?= htmlReady($input['name']) ?>" value="<?= htmlReady($input['value']) ?>" min="<?= $input['min'] ?>" <? if (!empty($input['must'])) echo 'required'; ?>>
<? endif;

if ($input['type'] === "textarea") : ?>
    <? if (!empty($input['i18n'])) : ?>
        <?= I18N::textarea($input['name'], $input['value'], $is_locked_array + $is_required_array) ?>
    <? else : ?>
        <textarea <?=$is_locked ?> name="<?= htmlReady($input['name']) ?>" <? if (!empty($input['must'])) echo 'required'; ?>><?=
            htmlReady($input['value'])
            ?></textarea>
    <? endif ?>
<? endif;

if ($input['type'] === "select") : ?>
    <? if (empty($input['choices'][$input['value']]) && empty($input['changable'])): ?>
        <?= _("Keine Änderung möglich") ?>
    <? else: ?>
    <select <?=$is_locked ?> name="<?= htmlReady($input['name']) ?>" <? if (!empty($input['must'])) echo 'required'; ?>>
<? foreach ($input['choices'] as $choice_value => $choice_name): ?>
    <? if (is_array($choice_name)): ?>
        <optgroup label="<?= htmlReady($choice_value) ?>">
        <? foreach ($choice_name as $c_v => $c_n): ?>
            <option value="<?= htmlReady($c_v) ?>" <? if (in_array($c_v, (array)$input['value'])) echo 'selected'; ?>>
                <?= htmlReady($c_n) ?>
            </option>
        <? endforeach; ?>
        </optgroup>
    <? else: ?>
        <option value="<?= htmlReady($choice_value) ?>" <? if (in_array($choice_value, (array)$input['value'])) echo 'selected'; ?>>
            <?= htmlReady($choice_name) ?>
        </option>
    <? endif; ?>
<? endforeach; ?>
    </select>
    <? endif; ?>
<? endif;

if ($input['type'] === "multiselect") : ?>
    <select <?=$is_locked ?> name="<?= htmlReady($input['name']) ?>" multiple class="nested-select" <? if (!empty($input['must'])) echo 'required'; ?>>
    <? if ($input['choices']) : foreach ($input['choices'] as $choice_value => $choice_name) : ?>
        <option value="<?= htmlReady($choice_value) ?>"<?=
            in_array($choice_value, is_array($input['value']) ? $input['value'] : [$input['value']])
            ? " selected"
            : "" ?>><?= htmlReady($choice_name) ?></option>
    <? endforeach; endif; ?>
    </select>
<? endif;

if ($input['type'] === 'nested-select'): ?>
<? if (isset($input['changable']) && !$input['changable']): ?>
        <?= _("Keine Änderung möglich") ?>
<? else: ?>
    <select <?= $is_locked ?> name="<?= htmlReady($input['name']) ?>" class="nested-select" <? if (!empty($input['must'])) echo 'required'; ?> <? if (!empty($input['multiple'])) echo 'multiple'; ?>>
  <? foreach ($input['choices'] as $outer_id => $group): ?>
    <? if ($group['label'] !== false): ?>
        <option value="<?= htmlReady($outer_id) ?>" class="nested-item-header" <? if (in_array($outer_id, (array)$input['value'])) echo 'selected'; ?>>
            <?= htmlReady($group['label']) ?>
        </option>
    <? endif; ?>
    <? foreach ($group['children'] as $inner_id => $inner_label): ?>
        <option value="<?= htmlReady($inner_id) ?>" class="nested-item" <? if (in_array($inner_id, (array)$input['value'])) echo 'selected'; ?>>
            <?= htmlReady($inner_label) ?>
        </option>
    <? endforeach; ?>
  <? endforeach; ?>
    </select>
<? endif;
endif;

if ($input['type'] === 'datafield'): ?>
    <? if ($is_locked): ?>
        <label>
            <?= htmlReady($input['title']) ?>

            <? if ($input['description']): ?>
                <?= tooltipIcon($input['description']) ?>
            <? endif?>

            <div>
                <?= $input['display_value'] ?>
            </div>
        </label>
    <? else: ?>
        <?= $input['html_value'] ?>
    <? endif ?>
<? endif;
