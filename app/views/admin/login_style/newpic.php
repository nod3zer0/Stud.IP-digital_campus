<?php
/**
 * @var Admin_LoginStyleController $controller
 */
?>
<form class="default" action="<?= $controller->add_pic() ?>" method="post" enctype="multipart/form-data">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend>
            <?= _('Bild(er) hinzufügen') ?>
        </legend>
        <label>
            <?= _('Bild(er) hochladen') ?>
            <input type="file"
                   name="pictures[]"
                   style="display: none;"
                   accept="image/gif,image/jpeg,image/png"
                   required
                   multiple>
            <?= Icon::create('upload')->asImg(['class' => 'text-bottom upload']) ?>
        </label>

        <label>
            <input type="checkbox" name="desktop" value="1" checked>
            <?= _('aktiv in Desktopansicht') ?>
        </label>

        <label>
            <input type="checkbox" name="mobile" value="1" checked>
            <?= _('aktiv in Mobilansicht') ?>
        </label>
    </fieldset>

    <footer data-dialog-button>
        <?= CSRFProtection::tokenTag() ?>
        <?= Studip\Button::createAccept(_('Speichern'), 'store') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->indexURL()) ?>
    </footer>
</form>
