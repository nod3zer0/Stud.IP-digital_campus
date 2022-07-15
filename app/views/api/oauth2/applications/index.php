<? if (isset($applications) && count($applications)) { ?>
    <? foreach ($applications as $application) { ?>
        <article class="studip">
            <header>
                <h1>
                    <a href="<?= $controller->link_for('api/oauth2/applications/details/' . $application['id']) ?>" data-dialog="size=auto">
                        <?= htmlReady($application['name']) ?>
                    </a>
                </h1>
                <nav>
                    <form
                        action ="<?= $controller->link_for('api/oauth2/applications/revoke') ?>"
                        method="post">
                        <?= CSRFProtection::tokenTag() ?>
                        <input type="hidden" name="application" value="<?= htmlReady($application['id']) ?>">
                        <?= ActionMenu::get()
                                      ->addButton(
                                          _('Autorisierung widerrufen'),
                                          'revoke_authorisation',
                                          Icon::create('trash'),
                                          [
                                              'data-confirm' => _('Wollen Sie die OAuth2-Autorisierung wirklich widerrufen?'),
                                              'title' => _('Autorisierung widerrufen'),
                                          ]
                                      )
                                      ->render() ?>
                    </form>
                </nav>
            </header>

            <div>
                <span class="oauth2-application--owned-by">
                    <?= _('Entwickelt durch:') ?>
                    <a rel="noreferrer noopener" target="_blank"
                        href="<?= htmlReady($application['homepage']) ?>">
                        <?= htmlReady($application['owner']) ?>
                    </a>
                </span>
            </div>

            <ul>
                <? foreach ($application['scopes'] as $scope) { ?>
                    <li><?= htmlReady($scope->description) ?></li>
                <? } ?>
            </ul>
        </article>
    <? } ?>
<? } else { ?>
    <?= \MessageBox::info(
        _('Keine autorisierten Drittanwendungen'),
        [ _('Sie haben keine Anwendungen, die zum Zugriff auf Ihr Konto berechtigt sind.') ]) ?>
<? } ?>
