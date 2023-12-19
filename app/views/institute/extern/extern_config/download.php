<?php
/**
 * @var ExternController $controller
 * @var ExternPageDownload $page
 * @var ExternPageConfig $config
 */
?>

<span class="content-title">
    <?= _('Konfiguration für eine Liste von Dateien (Download)') ?>
</span>

<form method="post" action="<?= $controller->store('Download', $config->id) ?>" class="default collapsable">
    <?= CSRFProtection::tokenTag() ?>
    <?= $this->render_partial('institute/extern/extern_config/_basic_settings') ?>

    <fieldset>
        <legend>
            <?= _('Angaben zum Inhalt') ?>
        </legend>
        <label class="col-3">
            <?= _('Sortierung') ?>
            <select name="sort" id="data_sort">
                <? foreach ($page->getSortFields() as $sort_field => $field_name) : ?>
                    <option value="<?= htmlReady($sort_field) ?>"
                        <? if ($sort_field === $page->sort) echo 'selected'; ?>>
                        <?= htmlReady($field_name) ?>
                    </option>
                <? endforeach ?>
            </select>
        </label>
        <label>
            <?= _('Ordnerauswahl') ?>
            <select class="nested-select" name="folder[]" multiple>
                <? foreach ($page->get_concatenated_folders() as $folder_id => $folder_name) : ?>
                    <option value="<?= htmlReady($folder_id) ?>"
                    <?= in_array($folder_id, (array) $page->folder) ? 'selected' : '' ?>>
                        <?= htmlReady($folder_name) ?>
                    </option>
                <? endforeach ?>
            </select>
        </label>
        <label>
            <input type="checkbox" name="subfolders" value="1"
                <?= $page->subfolders ? 'checked' : '' ?>>
            <?= _('Unterordner der ausgewählten Ordner anzeigen.') ?>
        </label>
    </fieldset>

    <?= $this->render_partial('institute/extern/extern_config/_template') ?>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\Button::createAccept(_('Speichern und zurück'), 'store_cancel') ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'), $controller->indexURL()
        ) ?>
    </footer>

</form>
