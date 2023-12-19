<?php
/**
 * @var ExternPageCourses $page
 */
?>

<label>
    <?= _('Veranstaltungstypen') ?>
    <select name="semtypes[]" class="nested-select" multiple>
         <? foreach (ExternPage::getGroupedSemTypes() as $class) : ?>
            <option class="nested-item-header" disabled><?= htmlReady($class['name']) ?></option>
            <? foreach ($class['types'] as $type) : ?>
                <option
                    class="nested-item"
                    value="<?= htmlReady($type['id']) ?>"
                    <?= in_array($type['id'], (array) $page->semtypes) ? ' selected' : '' ?>>
                    <?= htmlReady("{$type['name']} ({$class['name']})") ?>
                </option>
            <? endforeach ?>
        <? endforeach ?>
    </select>
</label>
