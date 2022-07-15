<section class="oauth authorize">
    <header>
        <h1><?= _('Autorisierungsanfrage') ?></h1>
    </header>

    <p>
        <?= sprintf(
            _('Die Applikation <strong>"%s"</strong> möchte auf Ihre Daten zugreifen.'),
            htmlReady($client->getName())
            ) ?>
    </p>

    <? if (count($scopes) > 0) { ?>
        <div class="scopes">
            <p><strong><?= _('Diese Applikation hat Zugriff auf:') ?></strong></p>
            <ul>
                <? foreach ($scopes as $scope) { ?>
                    <li><?= htmlReady($scope->description) ?></li>
                <? } ?>
            </ul>
        </div>
    <? } ?>

    <div class="buttons">
        <form action="<?= $controller->url_for('api/oauth2/authorize') ?>" method="post">
            <?= \CSRFProtection::tokenTag() ?>
            <input type="hidden" name="_method" value="delete">
            <input type="hidden" name="state" value="<?= htmlReady($state) ?>">
            <input type="hidden" name="client_id" value="<?= htmlReady($client->id) ?>">
            <input type="hidden" name="auth_token" value="<?= htmlReady($authToken) ?>">
            <?= Studip\Button::create(_('Verweigern'), 'deny') ?>
        </form>

        <form action="<?= $controller->url_for('api/oauth2/authorize') ?>" method="post">
            <?= \CSRFProtection::tokenTag() ?>
            <input type="hidden" name="state" value="<?= htmlReady($state) ?>">
            <input type="hidden" name="client_id" value="<?= htmlReady($client->id) ?>">
            <input type="hidden" name="auth_token" value="<?= htmlReady($authToken) ?>">
            <?= Studip\Button::create(_('Erlauben'), 'allow') ?>
        </form>
    </div>

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
