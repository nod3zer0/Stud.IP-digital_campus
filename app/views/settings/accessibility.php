<form method="post" action="<?= $controller->store() ?>" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend id="accessibility"><?= _('Barrierefreiheitseinstellungen') ?></legend>
        <label>
            <input type="checkbox" name="enable_high_contrast"
                   value="1"
                <? if ($config->USER_HIGH_CONTRAST) echo 'checked'; ?>>
            <?= _('Kontrastreiches Farbschema aktivieren') ?>
            <?= tooltipIcon(
                _('Mit dieser Einstellung wird ein Farbschema mit hohem Kontrast aktiviert.')
            ) ?>
        </label>
    </fieldset>

    <footer>
        <?= \Studip\Button::create(_('Speichern')) ?>
    </footer>
</form>
