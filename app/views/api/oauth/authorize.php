<section class="oauth authorize">
    <p>
        <?= sprintf(
            _('Die Applikation <strong>%s</strong> möchte auf Ihre Daten zugreifen.'),
            htmlReady($consumer->title)
        ) ?>
    </p>

    <form action="<?= $controller->url_for('api/oauth/authorize?oauth_token=' . $token) ?>" method="post">
        <input type="hidden" name="oauth_callback" value="<?= htmlReady($oauth_callback) ?>">
        <p>
            <?= Studip\Button::createAccept(_('Erlauben'), 'allow') ?>
            <?= Studip\LinkButton::createCancel(_('Verweigern'), $consumer->callback) ?>
        </p>
    </form>

    <p>
        <?= Avatar::getAvatar($GLOBALS['user']->id)->getImageTag(Avatar::SMALL) ?>

        <?= sprintf(
            _('Angemeldet als <strong>%s</strong> (%s)'),
            htmlReady($GLOBALS['user']->getFullName()),
            htmlReady($GLOBALS['user']->username)
        ) ?><br>
        <small>
            <a href="<?= URLHelper::getLink('logout.php') ?>">
                <?= sprintf(
                    _('Sind sie nicht <strong>%s</strong>, so melden Sie sich bitte ab und versuchen es erneut.'),
                    htmlReady($GLOBALS['user']->getFullName())
                ) ?>
            </a>
        </small>
    </p>
</section>
