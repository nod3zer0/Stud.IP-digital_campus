<form action="<?= $controller->link_for('admin/tree/do_batch_assign') ?>" method="post">
    <section>
        <?= $search->render() ?>
    </section>
    <input type="hidden" name="node" value="<?= htmlReady($node) ?>">
    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Zuordnen'), 'assign') ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), 'cancel', ['data-dialog' => 'close']) ?>
    </footer>
</form>
