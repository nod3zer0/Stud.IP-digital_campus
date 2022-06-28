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

        <label>
            <input type="checkbox" name="skiplinks_enable"
                   value="1"
                <? if ($config->SKIPLINKS_ENABLE) echo 'checked'; ?>>
            <?= _('Skiplinks einblenden') ?>
            <?= tooltipIcon(_('Mit dieser Einstellung wird nach dem ersten Drücken der Tab-Taste eine '
                .'Liste mit Skiplinks eingeblendet, mit deren Hilfe Sie mit der Tastatur '
                .'schneller zu den Hauptinhaltsbereichen der Seite navigieren können. '
                .'Zusätzlich wird der aktive Bereich einer Seite hervorgehoben.')) ?>
        </label>

    </fieldset>

    <footer>
        <?= \Studip\Button::create(_('Speichern')) ?>
    </footer>
</form>
