<?php
/**
 * @var TFASecret $secret
 * @var TfaController $controller
 * @var bool $own_profile
 */
?>
<p>
    <?= _('Zwei-Faktor-Authentifizierung ist aktiviert') ?>:
    <?= $secret->type == 'app' ? _('Authenticator-App') : _('E-Mail') ?>
</p>
<form action="<?= $controller->revoke() ?>" method="post">
    <?= Studip\Button::createAccept(_('Aufheben'), 'revoke', $own_profile ? [] : [
        'disabled' => ''
    ]) ?>
</form>
