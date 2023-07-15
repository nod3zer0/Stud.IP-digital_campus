<div id="file_suggest_oer">
    <form action="<?= $controller->link_for('file/suggest_oer/' . $file_ref_id) ?>"
          class='default' method='POST' data-dialog="reload-on-close">
        <?= CSRFProtection::tokenTag() ?>
        <p class="suggestion_text"><?= _('Das Material gefällt Ihnen?') ?></p>
        <p class="suggestion_text"><?= _('Schlagen Sie es zum Teilen im OER Campus vor.') ?></p>

        <p><?= _('Schreiben Sie der Autorin/dem Autoren:') ?></p>

        <label for="additional_text">
            <textarea   class = "add_toolbar wysiwyg"
                        name  = "additional_text"
                        id    = "additional_text"
                        rows  = "3"
                        placeholder = "<?= _('Warum gefällt Ihnen das Material?') ?>"
            ></textarea>
        </label>
        <p id="note">
            <?= _('Eine Nachricht ist freiwillig. Ihr Vorschlag wird anonym versendet.') ?>
        </p>

        <div id="oer_file_details">

            <div id="preview_container" class="oercampus_editmaterial">
                <div class="hgroup">
                    <label for="oer_logo_uploader">
                        <article class="contentbox" title="">
                            <header>
                                <h1>
                                    <studip-icon shape="<?= $icon_shape ?>"
                                                 :size="20"
                                                 class="text-bottom">
                                    </studip-icon>
                                    <div id="oer_title">
                                        <?= htmlReady(strlen($file->getFilename()) > 45 ? substr($file->getFilename(), 0, 45) . '...' : $file->getFilename()) ?>
                                    </div>
                                </h1>
                            </header>
                            <div id="oer_preview_image">
                                <?= Icon::create($icon_shape)->asimg(160) ?>
                            </div>
                        </article>
                    </label>

                </div>
            </div>

            <aside id="oer_aside">
                <table class="default nohover">
                    <caption><?= _('Informationen') ?></caption>
                    <tbody>
                    <tr>
                        <td><?= _('Größe') ?></td>
                        <? $size = $file->getSize() ?>
                        <td><?= $size !== null ? relSize($file->getSize(), false) : '-' ?></td>
                    </tr>
                    <tr>
                        <td><?= _('Downloads') ?></td>
                        <td><?= $file->getDownloads() ?></td>
                    </tr>
                    <tr>
                        <td><?= _('Erstellt') ?></td>
                        <td><?= date('d.m.Y H:i', $file->getMakeDate()) ?></td>
                    </tr>
                    <tr>
                        <td><?= _('Geändert') ?></td>
                        <td><?= date('d.m.Y H:i', $file->getLastChangeDate()) ?></td>
                    </tr>
                    <tr>
                        <td><?= _('Besitzer/-in') ?></td>
                        <td>
                            <? $user_id = $file->getUserId() ?>
                            <? if ($user_id) : ?>
                                <a href="<?= URLHelper::getLink('dispatch.php/profile', ['username' => get_username($user_id)]) ?>">
                                    <?= htmlReady($file->getUserName()) ?>
                                </a>
                            <? else : ?>
                                <?= htmlReady($file->getUserName()) ?>
                            <? endif ?>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </aside>

        </div>
        <footer data-dialog-button>
            <?= Studip\Button::create(_('Teilen vorschlagen'))?>
        </footer>
    </form>
</div>
