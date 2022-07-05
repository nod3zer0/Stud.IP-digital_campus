<div id="file_suggest_oer">
    <form action='<?= $controller->url_for('file/suggest_oer/' . $file_ref_id)?>'
          class='default' method='POST' data-dialog="reload-on-close">
        <?= CSRFProtection::tokenTag() ?>
        <p><?= sprintf(_('Das Material gefällt Ihnen? – Schlagen Sie es zur Veröffentlichung im OER-Campus vor.
        Schreiben Sie dem/der Autor*in %s, warum er/sie das Material veröffentlichen sollte.'), $author_fullname)?></p>
        <label for="additional_text">
            <span class="">
                <?= _("Ihr Vorschlag wird anonym versendet.") ?>
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
</div>
<?= $this->render_partial('file/file_details.php') ?>
