<?php

use Studip\Button;

/**
 * @var bool $hidden
 * @var string $uname;
 */
$username_tooltip_text = (string)Config::get()->USERNAME_TOOLTIP_TEXT;
$password_tooltip_text = (string)Config::get()->PASSWORD_TOOLTIP_TEXT;
?>

<form class="default <?= $hidden ? 'hide' : '' ?>"
      name="login_form"
      id="login-form"
      method="post"
      action="<?= URLHelper::getLink(Request::url(), ['cancel_login' => null]) ?>"
      <? if ($hidden) echo 'hidden'; ?>
>
    <section>
        <label>
            <span class="required"><?= _('Benutzername') ?></span>
            <? if ($username_tooltip_text) : ?>
                <?= tooltipIcon($username_tooltip_text) ?>
            <? endif ?>
            <input type="text" <?= (mb_strlen($uname) || $hidden) ? '' : 'autofocus' ?>
                   id="loginname"
                   name="loginname"
                   value="<?= htmlReady($uname) ?>"
                   size="20"
                   spellcheck="false"
                   autocapitalize="off"
                   autocomplete="username"
                   required>
        </label>
        <label for="password" style="position: relative">
            <span class="required"><?= _('Passwort') ?></span>
            <? if ($password_tooltip_text) : ?>
                <?= tooltipIcon($password_tooltip_text) ?>
            <? endif ?>
            <input type="password" <?= mb_strlen($uname) && !$hidden ? 'autofocus' : '' ?>
                   id="password"
                   name="password"
                   autocomplete="current-password"
                   size="20"
                   required>

            <i id="password-toggle" tabindex="0" aria-role="button" class="enter-accessible">
                <?= Icon::create('visibility-checked')->asImg(20, [
                    'id   ' => 'visible-password',
                    'title' => _('Passwort anzeigen'),
                ]) ?>
                <?= Icon::create('visibility-invisible')->asImg(20, [
                    'id'    => 'invisible-password',
                    'style' => 'display: none',
                    'title' => _('Passwort verstecken'),
                ]) ?>
            </i>

        </label>
        <p id="password-caps" style="display: none"><?= _('Feststelltaste ist aktiviert!') ?></p>
    </section>

    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="login_ticket" value="<?= Seminar_Session::get_ticket() ?>">
    <input type="hidden" name="resolution"  value="">

    <div id="<?=$login_footer_id?>">
        <?= Button::createAccept(_('Anmelden'), _('Login'), ['id' => 'submit_login']); ?>

        <? if (Config::get()->ENABLE_REQUEST_NEW_PASSWORD_BY_USER && in_array('Standard', $GLOBALS['STUDIP_AUTH_PLUGIN'])): ?>
            <a style="line-height: 1 !important" href="<?= URLHelper::getLink('dispatch.php/new_password?cancel_login=1') ?>">
            <? else: ?>
            <a style="line-height: 1 !important" href="mailto:<?= $GLOBALS['UNI_CONTACT'] ?>?subject=<?= rawurlencode('Stud.IP Passwort vergessen - '.Config::get()->UNI_NAME_CLEAN) ?>&amp;body=<?= rawurlencode('Ich habe mein Passwort vergessen. Bitte senden Sie mir ein Neues.\nMein Nutzername: ' . htmlReady($uname) . "\n") ?>">
                <? endif; ?>
                <?= _('Passwort vergessen?') ?>
            </a>
    </div>
</form>
