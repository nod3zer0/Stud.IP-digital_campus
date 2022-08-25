<form action="<?= $controller->link_for("oer/market/abo") ?>"
      method="post">

    <input type="hidden" name="abo" value="0">

    <label>
        <input type="checkbox" name="abo" value="1"<?= $abo ? " checked" : "" ?>>
        <?= _('Ich möchte Nachrichten bekommen über neue Inhalte im OER Campus') ?>
    </label>

    <div data-dialog-button>
        <?= \Studip\Button::create(_('Speichern')) ?>
    </div>

</form>
