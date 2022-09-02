<form action="<?= $list['stop'] ? $controller->action_link('index') : $controller->action_link('chooser'); ?>" style="width: 100%;" id="<?= htmlReady($name) ?>">
    <? if (is_array($list['elements']) && sizeof($list['elements'])) : ?>
    <input type="hidden" name="step" value="<?= htmlReady($name) ?>">
    <? if ($list['stop']) : ?>
    <input type="hidden" name="stop" value="1">
    <? endif; ?>
    <label><?= $list['headline'] ?>
        <select name="id" style="width: 100%;">
            <option value="">-- <?= _('Bitte wählen') ?> --</option>
        <? foreach ($list['elements'] as $key => $element) : ?>
            <option value="<?= htmlReady($key) ?>"<?= $key == $list['selected'] ? ' selected' : '' ?>>
                <?= htmlReady($element['name']) ?>
            </option>
        <? endforeach; ?>
        </select>
    </label>
    <? endif; ?>
</form>
