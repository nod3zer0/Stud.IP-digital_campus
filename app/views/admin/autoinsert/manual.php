<?php
/**
 * @var Admin_AutoinsertController $controller
 * @var string $sem_search
 * @var string $sem_select
 * @var array $seminar_search
 * @var array $filtertype
 * @var string $sem_id
 * @var array $available_filtertypes
 * @var array $values
 * @var array $filter
 */
?>

<style type="text/css">
    .filter_selection select {
        width: 100%;
    }

    .filter_selection input[name=remove_filter] {
        float: right;
    }
</style>

<form class="default" action="<?= $controller->manual() ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <?= $this->render_partial('admin/autoinsert/_search.php', compact('sem_search', 'sem_select')) ?>
</form>


<? if ((is_array($seminar_search) && count($seminar_search) > 0) && $sem_search && $sem_select): ?>
    <form class="default" action="<?= $controller->manual() ?>" method="post">
        <?= CSRFProtection::tokenTag() ?>
        <input type="hidden" name="sem_search" value="<?= htmlReady($sem_search) ?>">
        <input type="hidden" name="sem_select" value="<?= htmlReady($sem_select) ?>">
        <? foreach ($filtertype as $type): ?>
            <input type="hidden" name="filtertype[]" value="<?= $type ?>">
        <? endforeach; ?>

        <table class="default">
            <colgroup>
                <col width="17%">
                <col width="33%">
                <col width="50%">
            </colgroup>
            <thead>
            <tr>
                <th colspan="3"><?= _('Suchergebnisse') ?></th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <label for="sem_id"><?= _('Veranstaltung') ?></label>
                </td>
                <td colspan="2">
                    <select name="sem_id" id="sem_id" style="width: 100%;">
                        <? foreach ($seminar_search as $seminar): ?>
                            <option
                                value="<?= $seminar[0] ?>" <?= ($sem_id == $seminar[0]) ? 'selected="selected"' : '' ?>>
                                <?= htmlReady($seminar[1]) ?>
                            </option>
                        <? endforeach; ?>
                    </select>
                </td>
            </tr>
            <? if (count($filtertype) != count($available_filtertypes)): ?>
                <tr>
                    <td>
                        <legend for="add_filtertype"><?= _('Filterkriterien') ?></legend>
                    </td>
                    <td colspan="2">
                        <select name="add_filtertype">
                            <? foreach ($available_filtertypes as $key => $value): ?>
                                <? if (!in_array($key, $filtertype)): ?>
                                    <option value="<?= $key ?>"><?= $value ?></option>
                                <? endif ?>
                            <? endforeach; ?>
                        </select>
                        <?= Icon::create(
                            'add',
                            Icon::ROLE_CLICKABLE,
                            ['title' => _('Filter hinzufügen')]
                        )->asInput(["type" => "image", "class" => "middle", "name" => "add_filter"]) ?>
                    </td>
                </tr>
            <? endif ?>
            </tbody>

            <!-- #2 Auswahllisten anzeigen -->
            <? if (!empty($filtertype)): ?>
                <tbody class="default filter_selection" style="vertical-align: top;">
                <tr>
                    <th colspan="3"><?= _('Ausgewählte Filterkriterien') ?></th>
                </tr>
                <? $index = 0;
                foreach ($filtertype

                as $type): ?>
                <? if ($index % 2 == 0): ?>
                <? if ($index != 0): ?></tr><? endif ?>
                <tr>
                    <? endif ?>
                    <td colspan="<?= $index % 2 ? 1 : 2 ?>">
                        <label for="<?= $type ?>"><b><?= $available_filtertypes[$type] ?></b></label>
                        <?= Icon::create(
                            'remove',
                            Icon::ROLE_CLICKABLE,
                            ['title' => _('Filter entfernen')]
                        )->asInput(["type" => "image", "class" => "middle", "name" => "remove_filter[" . $type . "]"]) ?>
                        <br>

                        <select name="filter[<?= $type ?>][]" multiple size="5" class="nested-select">
                            <? foreach ($values[$type] as $key => $value): ?>
                                <? if (is_array($value)): ?>
                                    <option value="<?= $key ?>"
                                            class="nested-item-header" <?= in_array($key, (array)@$filter[$type]) ? 'selected="selected"' : '' ?>><?= htmlReady($value['name']) ?></option>
                                    <? foreach ($value['values'] as $k => $v): ?>
                                        <option value="<?= $k ?>"
                                                class="nested-item" <?= in_array($k, (array)@$filter[$type]) ? 'selected="selected"' : '' ?>><?= htmlReady($v) ?></option>
                                    <? endforeach; ?>
                                <? else: ?>
                                    <option
                                        value="<?= $key ?>" <?= in_array($key, (array)@$filter[$type]) ? 'selected="selected"' : '' ?>><?= htmlReady($value) ?></option>
                                <? endif ?>
                            <? endforeach; ?>
                        </select>
                    </td>
                    <? $index++;
                    endforeach; ?>
                    <? if ($index % 2 != 0): ?>
                        <td>&nbsp;</td>
                    <? endif ?>
                </tr>
                </tbody>
            <? endif ?>
            <thead>
                <tr>
                    <th colspan="3"><?= _('Einstellungen') ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3">
                        <label>
                            <input type="checkbox" name="force" value="1">
                            <?= _('Eintragung forcieren') ?>
                            <?= tooltipIcon(implode("\n", [
                                _('Über diese Einstellung kann forciert werden, dass alle gefundenen Personen in die Veranstaltung eingetragen werden.'),
                                _('Ansonsten werden nur die Personen eingetragen, die bislang noch nicht über diesen Mechanismus eingetragen wurden.'),
                            ])) ?>
                        </label>
                    </td>
                </tr>
            </tbody>
            <tfoot>
            <tr>
                <td colspan="3">
                    <?= Studip\Button::create(_('Eintragen'), 'submit') ?>
                    <?= Icon::create(
                        'question-circle',
                        Icon::ROLE_CLICKABLE,
                        ['title' => _('Vorschau')]
                    )->asInput(["type" => "image", "style" => "vertical-align: middle;", "name" => "preview"]) ?>
                </td>
            </tr>
            </tfoot>
        </table>


    </form>

    <script type="text/javascript">
        jQuery(function ($) {
            $('input[name=preview]').show().click(function (event) {
                if (!$(this).next().length || !$(this).next().is('span')) {
                    $(this).after($('<span id="autoinsert_count" style="vertical-align: middle;"/>'));
                }
                $.getJSON(
                    '<?= $controller->manual_count() ?>',
                    $(this).closest('form').serializeArray()
                ).done(function (json) {
                    let result = '';
                    if (!json || json.error) {
                        result = '<?= _('Fehler') ?>: ';
                        result += json.error || '<?= _('Fehler bei der Übertragung') ?>';
                    } else {
                        result = '<?= _('Gefundene Personen') ?>: ';
                        result += "<strong>" + json.users + "</strong>";
                    }
                    $('#autoinsert_count').html(' ' + result);
                });
                event.preventDefault();
            });
            $('input[name^=remove_filter]').click(function (event) {
                return confirm('<?= _('Wollen Sie diesen Filter wirklich entfernen?') ?>');
            });
        });
    </script>
<? endif ?>
