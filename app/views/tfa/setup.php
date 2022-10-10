<?php
/**
 * @var TfaController $controller
 */
?>
<form class="default" action="<?= $controller->create() ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Zwei-Faktor-Authentifizierung einrichten') ?></legend>

        <?= formatReady(Config::get()->TFA_TEXT_INTRODUCTION) ?>

        <label>
            <input required type="radio" name="type" value="email">
            <?= _('E-Mail') ?>
        </label>

        <label>
            <input required type="radio" name="type" value="app">
            <?= _('Authenticator-App') ?>
        </label>
    </fieldset>

    <footer>
        <?= Studip\Button::createAccept(_('Aktivieren')) ?>
    </footer>
</form>
