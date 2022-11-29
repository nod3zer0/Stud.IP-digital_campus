<form method="post">
    <?= CSRFProtection::tokenTag() ?>
    <div>
        <select name="selected_clipboard_id" class="clipboard-selector"
                <?= $clipboards ? '' : 'disabled="disabled"' ?>>
            <? if ($clipboards): ?>
                <? foreach ($clipboards as $clipboard): ?>
                    <option value="<?= htmlReady($clipboard->id) ?>"
                            <?= $clipboard->id == $selected_clipboard_id
                              ? 'selected="selected"'
                              : '' ?>>
                        <?= htmlReady($clipboard->name) ?>
                    </option>
                <? endforeach ?>
            <? endif ?>
        </select>
            <input class="clipboard-name invisible" type="text" name="clipboard_name" value="">
            <?= Icon::create('edit')->asInput(
                [
                    'data-widget-id' => $clipboard_widget_id,
                    'class' => 'middle clipboard-edit-button' . ($clipboards ? '' : ' invisible')
                ]
            ) ?>

            <?= Icon::create('accept')->asInput(
                [
                    'class' => 'middle clipboard-edit-accept invisible',
                    'data-widget-id' => $clipboard_widget_id
                ]
            ) ?>

            <?= Icon::create('decline')->asInput(
                [
                    'data-widget-id' => $clipboard_widget_id,
                    'class' => 'middle clipboard-edit-cancel invisible',
                ]
            ) ?>

            <?= Icon::create('trash')->asInput(
                [
                    'class' => 'middle clipboard-remove-button' . ($clipboards ? '' : ' invisible'),
                    'data-confirm-message' => _('Sind Sie sicher?')
                ]
            ) ?>
    </div>
    <div class="clipboard-area-container">
        <? if ($clipboards): ?>
            <? foreach ($clipboards as $clipboard): ?>
                <table id="Clipboard_<?= htmlReady($clipboard->id) ?>"
                       class="clipboard-area <?= $clipboard->id != $selected_clipboard_id
                                               ? 'invisible'
                                               : '' ?>"
                       data-id="<?= htmlReady($clipboard->id) ?>">
                    <colgroup>
                        <col style="width: 70%">
                    </colgroup>
                    <? $items = $clipboard->getContent() ?>
                    <? if ($items): ?>
                        <? foreach ($items as $item): ?>
                            <?
                            $checkbox_id = sprintf(
                                'item_%1$s_%2$s_%3$s',
                                $clipboard->id,
                                $item['range_type'],
                                $item['range_id']
                            )
                            ?>
                            <? if ($special_item_template): ?>
                                <?= $this->render_partial(
                                    $special_item_template,
                                    [
                                        'item' => $item,
                                        'draggable_items' => $draggable_items,
                                        'checkbox_id' => $checkbox_id
                                    ]
                                ) ?>
                            <? else: ?>
                                <tr class="clipboard-item <?= $draggable_items
                                                            ? 'draggable'
                                                            : '' ?>"
                                    data-range_id="<?= htmlReady($item['range_id']) ?>">
                                    <td class="item-name"><?= htmlReady($item['name']) ?></td>
                                    <td class="actions">
                                    <?= Icon::create('trash')->asInput(
                                        [
                                            'title' => sprintf(_('%s löschen.'), $item['name']),
                                            'data-confirm-message' => _('Sind Sie sicher?'),
                                            'class' => 'text-bottom clipboard-item-remove-button'
                                        ]
                                    ) ?>
                                    </td>
                                </tr>
                            <? endif ?>
                        <? endforeach ?>
                    <? endif ?>
                    <tr class="empty-clipboard-message <?= $items ? 'invisible' : '' ?>">
                        <td>
                        <?= htmlReady($empty_clipboard_string) ?>
                        </td>
                    </tr>
                    <? if ($special_item_template): ?>
                        <?= $this->render_partial(
                            $special_item_template,
                            [
                                'item' => null,
                                'draggable_items' => $draggable_items
                            ]
                        ) ?>
                    <? else: ?>
                        <tr class="clipboard-item <?= $draggable_items
                                                    ? 'draggable'
                                                    : ''
                                                  ?> clipboard-item-template invisible"
                            data-range_id="">
                            <td class="item-name"></td>
                            <td class="item-actions">
                                <?= Icon::create('trash')->asInput(
                                    [
                                        'class' => 'text-bottom clipboard-item-remove-button'
                                    ]
                                ) ?>
                            </td>
                        </tr>
                    <? endif ?>
                </table>
            <? endforeach ?>
        <? endif ?>
        <table id="Clipboard_CLIPBOARD_ID"
               class="clipboard-area clipboard-template invisible"
               data-id="CLIPBOARD_ID">
            <colgroup>
                <col style="width: 80%">
            </colgroup>
            <tr class="empty-clipboard-message">
                <td>
                <?= htmlReady($empty_clipboard_string) ?>
                </td>
            </tr>
            <? if ($special_item_template): ?>
                <?= $this->render_partial(
                    $special_item_template,
                    [
                        'item' => null,
                        'draggable_items' => $draggable_items
                    ]
                ) ?>
            <? else: ?>
                <tr class="clipboard-item <?= $draggable_items
                                            ? 'draggable'
                                            : ''
                                          ?> clipboard-item-template invisible"
                    data-range_id="">
                    <td class="item-name"></td>
                    <td>
                        <?= Icon::create('trash')->asInput(
                            [
                                'class' => 'text-bottom clipboard-item-remove-button'
                            ]
                        ) ?>
                    </td>
                </tr>
            <? endif ?>
        </table>
    </div>
</form>
