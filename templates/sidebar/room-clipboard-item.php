<?php
/*
   Template parameters:
   - $draggable_items: bool: If the clipboard items shall be draggable or not
   - $item: Array|null: An associative array with item attributes or null.
   If $item is an associative array it must have the following structure:
   [
   'id' => The ID of the item.
   'name' => The name of the item.
   'range_id' => The range-ID of the item.
   'range_type' => The range type of the item.
   ]
   If $item is null this template switches to HTML template mode which means
   placeholders are generated for the item attributes inside the HTML code.
   Furthermore template specific classes are added to the li element.
   The placeholders are named as follows:
   'id' => ITEM_ID
   'name' => NAME
   'range_id' => RANGE_ID
   'range_type' => RANGE_TYPE
 */

$classes = 'clipboard-item ';
if ($draggable_items) {
    $classes .= 'draggable ';
}
if (!$item) {
    $classes .= 'clipboard-item-template invisible';
}
?>
<tr class="<?= htmlReady($classes) ?>"
    data-range_id="<?= htmlReady($item['range_id']) ?>">
    <td>
        <label>
            <input type="checkbox"
                   name="selected_clipboard_items[]"
                   title="<?= _('Diesen Raum auswählen.') ?>"
                   value="<?= htmlReady($item['id']) ?>"
                   <?= in_array($item['id'], $selected_clipboard_items)
                     ? 'checked="checked"'
                     : (!$selected_clipboard_items
                      ? 'checked="checked"'
                      : ''
                     ) ?>
                   >
            <?= htmlReady($item['name']) ?>
        </label>
    </td>
    <td class="actions">
        <a href="<?= Room::getLinkForAction('show', ($item ? $item['range_id'] : 'RANGE_ID')) ?>" data-dialog>
            <?= Icon::create(
                    'info',
                    Icon::ROLE_CLICKABLE,
                    [
                        'title' => _('Rauminformationen'),
                        'class' => 'text-bottom'
                    ])?>
        </a>
        <a href="<?= Room::getLinkForAction('semester_plan', ($item ? $item['range_id'] : 'RANGE_ID')) ?>" target="_blank">
            <?= Icon::create(
                    'timetable',
                    Icon::ROLE_CLICKABLE,
                    [
                        'title' => _('Semesterbelegung'),
                        'class' => 'text-bottom'
                    ]
            )?>
        </a>
        <? if (!$readonly): ?>
            <?= Icon::create('trash')->asInput(
                [
                    'data-confirm-message' => _('Sind Sie sicher?'),
                    'class' => 'text-bottom clipboard-item-remove-button'
                ]
            ) ?>
        <? endif ?>
    </td>
</tr>
