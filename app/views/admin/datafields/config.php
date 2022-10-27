<?php
/**
 * @var Admin_DatafieldsController $controller
 * @var DataField $struct
 */
?>
<form action="<?= $controller->url_for('admin/datafields/config/' . $struct->id) ?>" method="post" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Datenfeld konfigurieren') ?></legend>

        <label>
            <?= _('Inhalte') ?>
            <textarea name="typeparam"><?="\n"?><?= htmlReady(rtrim($struct->typeparam), false) ?></textarea>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'store') ?>
        <?= Studip\Button::create(_('Vorschau'), 'preview', ['data-dialog' => 'size=auto']) ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('admin/datafields/index/' . $struct->object_type . '#' . $struct->object_type)) ?>
    </footer>
</form>
