<?php
/**
 * @var Admin_ExternController $controller
 * @var ExternPagePersBrowse $page
 * @var ExternPageConfig $config
 */
?>

<span class="content-title">
    <?= _('Konfiguration für die Ausgabe von Veranstaltungen') ?>
</span>

<form method="post" action="<?= $controller->store('PersBrowse', $config->id) ?>" class="default collapsable">
    <?= CSRFProtection::tokenTag() ?>
    <?= $this->render_partial('institute/extern/extern_config/_basic_settings') ?>

    <fieldset>
        <legend>
            <?= _('Angaben zum Inhalt') ?>
        </legend>
        <label>
            <?= _('Rechtestufe in Einrichtungen') ?>
            <select name="instperms[]" class="nested-select" multiple>
                <? foreach ($page->getInstitutePermissionOptions() as $instperm_id => $instperm_name) : ?>
                    <option
                        value="<?= $instperm_id ?>"
                        <?= in_array($instperm_id, $page->instperms ?? []) ? ' selected' : '' ?>>
                        <?= htmlReady($instperm_name) ?>
                    </option>
                <? endforeach ?>
            </select>
        </label>
        <label>
            <input type="checkbox" name="onlylecturers" value="1"
                <?= $page->onlylecturers ? 'checked' : '' ?>>
            <?= _('Nur Lehrende anzeigen.') ?>
        </label>
        <?= $this->render_partial('institute/extern/extern_config/_institutes_selector') ?>
    </fieldset>

    <?= $this->render_partial('institute/extern/extern_config/_template') ?>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\Button::createAccept(_('Speichern und zurück'), 'store_cancel') ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'), $controller->url_for('institute/extern/index')
        ) ?>
    </footer>

</form>
