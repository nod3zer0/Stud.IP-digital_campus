<?php
/**
 * @var ExternPageCourses $page
 */
?>

<fieldset>
    <legend>
        <?= _('Studienbereiche') ?>
    </legend>
    <label>
        <?= _('Anzuzeigende Studienbereiche') ?>
        <?= tooltipIcon(_('Wenn keine Studienbereiche ausgewählt werden, werden alle angezeigt.')) ?>
        <? $study_area_paths = $page->getStudyAreaPaths() ?>
        <select name="studyareas[]" class="nested-select" id="select-study-areas" multiple>
            <? foreach ($study_area_paths as $area_id => $path) : ?>
                <option
                    value="<?= htmlReady($area_id) ?>"
                    selected class="nested-item"><?= htmlReady($path) ?>
                </option>
            <? endforeach ?>
        </select>
    </label>
    <label>
        <input type="checkbox" name="scope_kids" value="1"
            <?= $page->scope_kids ? 'checked' : '' ?>>
        <?= _('Unterebenen anzeigen.') ?>
    </label>
</fieldset>
