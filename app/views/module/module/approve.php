<form name="approve"
      action="<?= $controller->action_link('approve/' . $modul_id) ?>"
      method="post" class="default">
    
    <? $response = $controller->relay('shared/modul/description/' . $modul_id); ?>
    <?= $response->body ?>

    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Genehmigen'), 'approval') ?>
        <?= Studip\Button::createCancel(_('Abbrechen'), ['data-dialog' => 'close']) ?>
    </footer>
</form>
