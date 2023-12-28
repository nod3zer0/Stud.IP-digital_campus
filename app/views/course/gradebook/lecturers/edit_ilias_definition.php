<?php
/** @var StudipController $controller */
/** @var \Grading\Definition $definition */
?>
<form class="default" action="<?=$controller->link_for('course/gradebook/lecturers/edit_ilias_definition/' . $definition->id) ?>" method="POST">
    <?= CSRFProtection::tokenTag()?>
    <fieldset>
        <label>
            <?= _('Name der Leistung') ?>
            <input type="text" value="<?=htmlReady($definition->name)?>" name="test_name">
        </label>
        <label>
            <?=_('Prozentwert übertragen')?>
            <input type="checkbox" value="1" <?=substr($definition->item, -1) & 1 ? 'checked' : ''?> name="result">
        </label>
        <label>
            <?=_('Bestanden/nicht bestanden übertragen')?>
            <input type="checkbox" value="2" <?=substr($definition->item, -1) & 2 ? 'checked' : ''?> name="passed">
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= \Studip\Button::createAccept(_('Speichern')) ?>
        <?= \Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('course/gradebook/lecturers/edit_ilias_definitions')) ?>
    </footer>
</form>
