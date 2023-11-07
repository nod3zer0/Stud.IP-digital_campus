<form class="default a11y-settings" action="<?= $controller->url_for('admin/accessibility_info_text/edit') ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Infotext zu barrierefreien Dateien') ?></legend>
        <section>
            <label for="accessbility_info_text">
                <?= _('Die angegebene Information wird im Datei-Hochladen-Dialog unter der Checkbox angezeigt.') ?>
            </label>
            <?= I18N::textarea('accessbility_info_text', Config::get()->ACCESSIBILITY_INFO_TEXT,
                ['class' => 'wysiwyg', 'data-editor' => 'toolbar=small']) ?>
        </section>
    </fieldset>

    <footer data-dialog-button>
        <?= CSRFProtection::tokenTag() ?>
        <?= Studip\Button::createAccept(_('Speichern'), 'store') ?>
    </footer>
</form>
