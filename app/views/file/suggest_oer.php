<form action='<?= $controller->url_for('file/suggest_oer/' . $file_ref_id)?>'
      class='default' method='POST' data-dialog="reload-on-close">
    <?= CSRFProtection::tokenTag() ?>

    <p><?= sprintf(_('Das folgende Material wird %s zur Veröffentlichung im OER Campus vorgeschlagen:'), $author_fullname)?></p>
    <p><?= htmlReady($file->getFilename())?></p>
    <label for="additional_text">
        <span class="">
            <?= _("Ihr Vorschlag wird anonym versendet.") ?>
            <?= _("Falls gewünscht, können Sie zusätzlich eine Nachricht verfassen:") ?>
        </span>
        <textarea   name  = "additional_text"
                    id    = "additional_text"
                    rows  = "3"
        ></textarea>
    </label>

    <footer data-dialog-button>
        <?= Studip\Button::create(_("Material vorschlagen"))?>
    </footer>
</form>
