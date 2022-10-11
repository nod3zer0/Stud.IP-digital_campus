<?php
/**
 * @var Studiengaenge_StudiengaengeController $controller
 * @var Aufbaustudiengang $aufbaustg
 */
$perm = MvvPerm::get($aufbaustg);
?>
<form data-dialog action="<?= $controller->url_for('studiengaenge/studiengaenge/aufbaustg_store') ?>" class="default" id="mvv-aufbaustg-new" method="post">
    <input type="hidden" name="aufbaustg_id" value="<?= htmlReady($aufbaustg->id) ?>">
    <label for="mvv-aufbaustg-select">
        <?= _('Studiengang') ?>
    </label>
    
    <div data-dialog-button>
        <?= Studip\Button::createAccept(_('Speichern'), 'store_aufbaustg') ?>
        <?= Studip\LinkButton::createCancel(); ?>
    </div>
</form>
