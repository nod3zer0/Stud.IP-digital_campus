<?
    $sidebar = Sidebar::get();
    $actions = new ActionsWidget();
    $actions->addLink(
        _('OAuth2-Client hinzufügen'),
        $controller->url_for('api/oauth2/clients/add'),
        Icon::create('add')
    );
    $sidebar->addWidget($actions);
?>

<? if (isset($clients) && count($clients)) { ?>
    <h2>
        <?= _('Registrierte OAuth2-Clients') ?>
    </h2>

    <? foreach ($clients as $client) { ?>
        <article class="studip">
            <header>
                <h1>
                    <b><?= htmlReady($client['name']) ?></b>
                </h1>
                <nav>
                    <form
                        action ="<?= $controller->link_for('api/oauth2/clients/delete', $client) ?>"
                        method="post">
                        <?= CSRFProtection::tokenTag() ?>
                        <?= ActionMenu::get()
                                      ->addButton(
                                          sprintf(_('OAuth2-Client "%s" löschen'), $client['name']),
                                          'delete_client',
                                          Icon::create('trash'),
                                          [
                                              'data-confirm' => _('Wollen Sie den OAuth2-Client wirklich löschen?'),
                                              'title' => sprintf(_('OAuth2-Client "%s" löschen'), $client['name']),
                                          ]
                                      )
                                      ->render() ?>
                    </form>
                </nav>
            </header>

            <div>
                <dl>
                    <dt><?= _('Beschreibung') ?></dt>
                    <dd><?= htmlReady($client['description']) ?></dd>

                    <dt><?= _('Entwickelt durch') ?></dt>
                    <dd>
                        <a rel="noreferrer noopener" target="_blank"
                            href="<?= htmlReady($client['homepage']) ?>">
                            <?= htmlReady($client['owner']) ?>
                        </a>
                    </dd>

                    <dt><?= _('client_id') ?></dt>
                    <dd> <?= htmlReady($client['id']) ?> </dd>

                    <dt><?= _('Redirect-URIs') ?></dt>
                    <dd>
                        <ul>
                            <? foreach ($client->redirectUris() as $uri) { ?>
                                <li><?= htmlReady($uri) ?></li>
                            <? } ?>
                        </ul>
                    </dd>

                    <dt><?= _('Kann kryptographische Geheimnisse bewahren?') ?></dt>
                    <dd><?= $client->confidential() ? _('Ja') : _('Nein') ?></dd>

                    <dt><?= _('Notizen (nur für Root-Accounts sichtbar)') ?></dt>
                    <dd>
                        <?= htmlReady($client['admin_notes']) ?>
                    </dd>
                </dl>
            </div>
        </article>
    <? } ?>
<? } ?>
