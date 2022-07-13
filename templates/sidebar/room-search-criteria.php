<?php
/**
 * Template documentation:
 *
 * @param Array $criteria: A search criteria with the following structure:
 *     [
 *         'name' => The criteria's internal name.
 *         'type' => The type of the criteria:
 *                   'bool', 'num', 'select', 'date' or 'text'
 *         'range_search' => Whether a range search shall be used or not.
 *             This is only evaluated for the types 'date' and 'num'
 *         'value' => The value of the search criteria.
 *             For range search criteria the values are split by ':'.
 *     ]
 *
 * @param bool removable Whether the criteria can be removed or not.
 *     If the criteria can be removed a trash icon is shown.
 */
?>
<li class="item">
    <? if ($removable): ?>
        <?= Icon::create('trash')->asInput(
            [
                'title' => _('Kriterium entfernen'),
                'aria-label' => _('Kriterium entfernen'),
                'class' => 'text-bottom remove-icon'
            ]
        ) ?>
    <? endif ?>
    <? if ($criteria['type'] === 'bool'): ?>
        <input type="hidden" name="options_<?= htmlReady($criteria['name']) ?>" value="1">
        <label class="undecorated">
            <input type="checkbox"
                   value="1"
                    <?= Request::get($criteria['name']) ? 'checked': ''?>
                   name="<?= htmlReady($criteria['name'])?>">
            <span><?= htmlReady($criteria['title']) ?></span>
        </label>
    <? elseif ($criteria['type'] === 'num'): ?>
        <? if ($criteria['range_search']): ?>
            <label class="undecorated"><?= htmlReady($criteria['title']) ?></label>
            <div class="hgroup">
                <label class="undecorated">
                    <?= _('von') ?>
                    <input type="number"
                           name="<?= htmlReady($criteria['name'])?>_min"
                           value="<?= empty($criteria['value'][0])?'':intval($criteria['value'][0])?>">
                </label>
                <label class="undecorated">
                    <?= _('bis') ?>
                    <input type="number"
                           name="<?= htmlReady($criteria['name'])?>_max"
                           value="<?= empty($criteria['value'][1])?'':intval($criteria['value'][1])?>">
                </label>
            </div>
        <? else: ?>
            <label class="undecorated">
                <?= htmlReady($criteria['title']) ?>
                <input type="number" name="<?= htmlReady($criteria['name'])?>" value="<?= (int)$criteria['value']?>">
            </label>
        <? endif ?>
    <? elseif ($criteria['type'] === 'select'): ?>
        <label class="undecorated">
            <?= htmlReady($criteria['title']) ?>
            <select name="<?= htmlReady($criteria['name']) ?>">
                <? if (is_array($criteria['options'])): ?>
                    <? foreach ($criteria['options'] as $value => $title): ?>
                        <option value="<?= htmlReady($value) ?>"
                                <?= ($value == $criteria['value']
                                   ? 'selected="selected"'
                                   : '') ?>>
                            <?= htmlReady($title) ?>
                        </option>
                    <? endforeach ?>
                <? endif ?>
            </select>
        </label>
    <? elseif ($criteria['type'] === 'select2'): ?>
        <label class="undecorated">
            <?= htmlReady($criteria['title']) ?>
            <select name="<?= htmlReady($criteria['name']) ?>"
                    class="nested-select">
                <? if (is_array($criteria['options'])): ?>
                    <? foreach ($criteria['options'] as $option): ?>
                        <option value="<?= htmlReady($option['id']) ?>"
                                <?= ($option['id'] == $criteria['value']
                                ? 'selected="selected"'
                                : '') ?>>
                            <?= htmlReady($option['name']) ?>
                        </option>
                        <? foreach ($option['sub_options'] as $sub_option): ?>
                            <option value="<?= htmlReady($sub_option['id']) ?>"
                                    class="nested-item nested-level-1"
                                    <?= ($sub_option['id'] == $criteria['value']
                                    ? 'selected="selected"'
                                    : '') ?>>
                                <?= htmlReady($sub_option['name']) ?>
                            </option>
                        <? endforeach ?>
                    <? endforeach ?>
                <? endif ?>
            </select>
        </label>
    <? elseif ($criteria['type'] === 'hidden'): ?>
        <input type="hidden" name="<?= htmlReady($criteria['name'])?>" value="<?= htmlReady((string)$criteria['value'])?>">
    <? elseif ($criteria['type'] == 'disabled_text'): ?>
        <label class="undecorated">
            <span><?= htmlReady($criteria['title']) ?></span>
            <input type="text" disabled="disabled"
                name="<?= htmlReady($criteria['name'])?>" value="<?= htmlReady((string)$criteria['value'])?>">
        </label>
    <? else: ?>
        <label class="undecorated">
            <?= htmlReady($criteria['title']) ?>
            <input type="text" name="<?= htmlReady($criteria['name'])?>" value="<?= htmlReady((string)$criteria['value'])?>">
        </label>
    <? endif ?>
</li>
