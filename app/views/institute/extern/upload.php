<?php
/**
 * @var ExternController $controller
 * @var string $config_name
 */
?>

<form class="default" data-dialog="size=auto" method="post"
      enctype="multipart/form-data"
      action="<?= $controller->import()?>">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Konfigurationsdatei importieren') ?></legend>
        <label>
            <?= _('Name der Konfiguration') ?>
            <div style="font-size: smaller;">
                (<?= _('Ohne Angabe wird der Name aus den importierten Daten genommen.') ?>)
            </div>
            <input type="text" name="config_name" value="<?= htmlReady($config_name) ?>">
        </label>
        <label>
            <?= _('Konfigurationsdatei') ?>
            <input type="file" name="config_file" required="required">
        </label>
    </fieldset>
    <div data-dialog-button>
        <?= \Studip\Button::create(_('Importieren'), 'import') ?>
    </div>
</form>
