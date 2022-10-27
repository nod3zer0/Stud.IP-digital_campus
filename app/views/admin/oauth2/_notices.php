<?php
/**
 * @var Admin_Oauth2Controller $controller
 * @var Studip\OAuth2\Models\Client[] $clients
 */
?>
<? if (!isset($clients) || !count($clients)) { ?>
    <?= MessageBox::info(
        _('Es wurde noch kein OAuth2-Client erstellt.') .
        '<br/>' .
        \Studip\LinkButton::createAdd(
            _('OAuth2-Client hinzufügen'),
            $controller->link_for('api/oauth2/clients/add')
        )
        ) ?>
<? } ?>
