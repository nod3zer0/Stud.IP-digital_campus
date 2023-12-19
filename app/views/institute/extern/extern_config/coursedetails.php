<?php
/**
 * @var ExternController $controller
 * @var ExternPageConfig $config
 */
?>

<span class="content-title">
    <?= _('Konfiguration für die Detailansicht einer Veranstaltung') ?>
</span>

<form method="post" action="<?= $controller->store('CourseDetails', $config->id) ?>" class="default collapsable">
    <?= CSRFProtection::tokenTag() ?>
    <?= $this->render_partial('institute/extern/extern_config/_basic_settings') ?>
    <fieldset>
        <legend>
            <?= _('Angaben zum Inhalt') ?>
        </legend>
        <label>
            <?= _('Bereichspfad ab Ebene') ?>
            <?= tooltipIcon(_('Wählen Sie, ab welcher Ebene der Bereichspfad ausgegeben werden soll.')) ?>
            <input min="1" max="10" type="number" name="rangepathlevel" value="<?= $page->rangepathlevel ?? 1 ?>">
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
