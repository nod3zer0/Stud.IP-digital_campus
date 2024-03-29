<? use Studip\Button, Studip\LinkButton; ?>
<form data-dialog="size=auto" action="<?= $controller->link_for('calendar/calendar/publish/') ?>" method="post" class="default">
    <input type="hidden" name="studip_ticket" value="<?= get_ticket() ?>">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= _('Kalender mit anderen Teilen und in andere Kalender einbetten') ?>
        </legend>

        <? if (!$short_id) : ?>
            <?= _('Sie können sich eine Adresse generieren lassen, mit der Sie Termine aus Ihrem Stud.IP-Terminkalender in externen Terminkalendern einbinden können.') ?><br>
        <? else : ?>
            <?= _('Die folgende Adresse können Sie in externe Terminkalenderanwendungen eintragen, um Ihre Termine dort anzuzeigen:') ?>
            <? $url = URLHelper::getLink($GLOBALS['ABSOLUTE_URI_STUDIP'] . 'dispatch.php/ical/index/' . $short_id, null, true) ?>
            <div style="font-weight: bold;">
                <a href="<?= $url ?>" target="_blank"><?= htmlReady($url) ?></a>
            </div>

            <section>
                <?=  _('Verschicken Sie die Export-Adresse als E-Mail:') ?>
                <input type="email" name="email" value="<?= htmlReady($GLOBALS['user']->email) ?>" required="required">
                <?= Button::create(_('Abschicken'), 'submit_email', ['title' => _('Abschicken')]) ?>
            </section>

            <section>
                <?= _('Sie haben außerdem die Möglichkeit, sich eine neue Adresse generieren zu lassen.') ?>
                <?= _('Achtung: Die alte Adresse wird damit ungültig!') ?>
            </section>

            <section>
                <?= _('Sie können die Adresse auch löschen.') ?>
                <?= _('Ein Zugriff auf Ihre Termine über diese Adresse ist dann nicht mehr möglich!') ?>
            </section>
        <? endif; ?>
    </fieldset>

    <footer data-dialog-button>
        <? if (!$short_id) : ?>
            <?= Button::create(_('Adresse generieren'), 'new_id') ?>
        <? else :?>
            <?= Button::create(_('Neue Adresse generieren'), 'new_id', [
                'title' => _('Achtung: Die alte Adresse wird damit ungültig!')
            ]) ?>

            <?= Button::create(_('Adresse löschen'), 'delete_id', [
                'title' => _('Ein Zugriff auf Ihre Termine über diese Adresse ist dann nicht mehr möglich!')
            ]) ?>
        <? endif ?>

        <? if (!Request::isXhr()) : ?>
            <?= LinkButton::create(_('Abbrechen'), $controller->url_for('calendar/calendar')) ?>
        <? endif; ?>
    </footer>
</form>
