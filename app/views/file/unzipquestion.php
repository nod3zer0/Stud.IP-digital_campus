<form action="<?= $controller->url_for('file/unzipquestion') ?>" method="post" data-dialog>

<? foreach ($files as $file): ?>
    <input type="hidden" name="file_refs[]" value="<?= htmlReady($file->getId()) ?>">
<? endforeach ?>

    <?= Icon::create('unit-test', Icon::ROLE_INACTIVE)->asImg(120, ['style' => 'display: block; margin-left: auto; margin-right: auto;']) ?>

    <?= _('Soll diese ZIP-Datei entpackt werden?') ?>

    <footer data-dialog-button>
        <?= Studip\Button::create(_('Entpacken'), 'unzip') ?>
        <?= Studip\Button::create(_('Nicht Entpacken'), 'dontunzip') ?>
    </footer>
</form>
