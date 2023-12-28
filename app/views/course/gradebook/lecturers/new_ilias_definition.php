<?php
/** @var StudipController $controller */
/** @var array $ilias_modules */
?>
<form class="default" action="<?=$controller->link_for('course/gradebook/lecturers/create_ilias_definition') ?>" method="POST">
    <?= CSRFProtection::tokenTag()?>
    <fieldset>
        <label>
            <?= _('Bitte wählen Sie einen Test aus') ?>
            <select name="ilias_module">
            <? foreach ($ilias_modules as $key => $modules) : ?>
                <? foreach ($modules as $module) : ?>
                <option value="<?=$key . '-' . $module->getId()?>"><?=htmlReady($module->getTitle())?></option>
                <? endforeach;?>
            <? endforeach;?>
            </select>
        </label>
        <label>
            <?=_('Prozentwert übertragen')?>
            <input type="checkbox" value="1" checked name="result">
        </label>
        <label>
            <?=_('Bestanden/nicht bestanden übertragen')?>
            <input type="checkbox" value="2" checked name="passed">
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= \Studip\Button::createAccept(_('Speichern')) ?>
        <?= \Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('course/gradebook/lecturers/edit_ilias_definitions')) ?>
    </footer>
</form>
