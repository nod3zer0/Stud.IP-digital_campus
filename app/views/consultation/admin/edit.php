<form action="<?= $controller->store_edited($block, $page) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <?= MessageBox::info(
        _('Das Ändern der Informationen wird auch alle Termine dieses Blocks ändern.')
    )->hideClose() ?>

    <fieldset>
        <legend><?= _('Terminblock bearbeiten') ?></legend>

        <label>
            <span class="required"><?= _('Ort') ?></span>
            <input required type="text" name="room" placeholder="<?= _('Ort') ?>"
                   value="<?= htmlReady($block->room) ?>">
        </label>

        <label>
            <?=_('Information zu den Terminen in diesem Block') ?> (<?= _('Öffentlich einsehbar') ?>)
            <textarea name="note"><?= htmlReady($block->note ) ?></textarea>
        </label>

    <? if ($responsible): ?>
        <?= $this->render_partial('consultation/admin/block-responsibilities.php', compact('responsible', 'block')) ?>
    <? endif; ?>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern')) ?>
        <?= Studip\LinkButton::createCancel(
            _('Abbrechen'),
            $controller->indexURL($page)
        ) ?>
    </footer>
</form>
