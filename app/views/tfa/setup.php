<?php
/**
 * @var TfaController $controller
 * @var bool $own_profile
 */
?>
<form class="default" action="<?= $controller->create() ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend><?= _('Zwei-Faktor-Authentifizierung einrichten') ?></legend>

        <?= formatReady(Config::get()->TFA_TEXT_INTRODUCTION) ?>

        <label>
            <input required type="radio" name="type" value="email"
                   <? if (!$own_profile) echo 'disabled'; ?>>
            <?= _('E-Mail') ?>
        </label>

        <label>
            <input required type="radio" name="type" value="app"
                   <? if (!$own_profile) echo 'disabled'; ?>>
            <?= _('Authenticator-App') ?>
        </label>
    </fieldset>

    <footer>
        <?= Studip\Button::createAccept(_('Aktivieren'), 'activate', $own_profile ? [] : [
            'disabled' => ''
        ]) ?>
    </footer>
</form>
