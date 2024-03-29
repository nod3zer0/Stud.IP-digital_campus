<?php
/**
 * @var array $userSelectedElements
 * @var DatafieldEntryModel[] $datafields
 */
?>
<form class="default" method="post"
    action="<?= URLHelper::getLink('dispatch.php/admin/courses/sidebar'); ?>" >
    <input type="hidden" name="updateConfig" value="1">
    <fieldset>
        <legend><?= _('Standardfelder') ?></legend>
        <label>
            <input name="searchActive" type="checkbox" value="1"
                <?= (!empty($userSelectedElements['search'])) ? 'checked' : '' ?>
                >
            <?= _('Freie Suche'); ?>
        </label>

        <label>
            <input name="semesterActive" type="checkbox" value="1"
                <?= !empty($userSelectedElements['semester']) ? 'checked' : '' ?>
                >
            <?= _('Semester'); ?>
        </label>
        <label>
            <input name="instituteActive" type="checkbox" value="1"
                <?= !empty($userSelectedElements['institute']) ? 'checked' : '' ?>
                >
            <?= _('Einrichtung'); ?>
        </label>
        <label>
            <input name="stgteilActive" type="checkbox" value="1"
                <?= (!empty($userSelectedElements['stgteil'])) ? 'checked' : '' ?>
                >
            <?= _('Studiengangteil'); ?>
        </label>
        <label>
            <input name="teacherActive" type="checkbox" value="1"
                <?= (!empty($userSelectedElements['teacher'])) ? 'checked' : '' ?>
            >
            <?= _('Lehrperson'); ?>
        </label>
        <label>
            <input name="courseTypeActive" type="checkbox" value="1"
                <?= !empty($userSelectedElements['courseType']) ? 'checked' : '' ?>
                >
            <?= _('Veranstaltungstypfilter'); ?>
        </label>
    </fieldset>
    <? if ($datafields): ?>
    <fieldset>
        <legend><?= _('Datenfelder') ?></legend>
        <? foreach ($datafields as $datafield): ?>
        <label>
            <input name="activeDatafields[]" type="checkbox" value="<?= htmlReady($datafield->id) ?>"
                <? if (!empty($userSelectedElements['datafields'])) : ?>
                <?= in_array($datafield->id, $userSelectedElements['datafields']) ? 'checked="checked"' : '' ?>
                <? endif ?>
                >
            <?= htmlReady($datafield->name) ?>
        </label>
        <? endforeach ?>
    </fieldset>
    <? endif ?>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Speichern')); ?>
    </div>
</form>
