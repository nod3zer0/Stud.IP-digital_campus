<?php
/**
 * @var Studiengaenge_VersionenController $controller
 * @var StgteilAbschnitt $abschnitt
 * @var string $cancel_url
 */

use Studip\Button, Studip\LinkButton;
$perm = MvvPerm::get($abschnitt)
?>

<form class="default" action="<?= $controller->action_link('abschnitt/' . $abschnitt->id) ?>" method="post">
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?= _('Grunddaten') ?></legend>
        <label><?= _('Name') ?>
            <?= MvvI18N::input('name', $abschnitt->name, ['maxlength' => '254'])->checkPermission($abschnitt) ?>
        </label>
        <label>
            <?= _('Kommentar') ?>
            <?= MvvI18N::textarea('kommentar', $abschnitt->kommentar, ['class' => 'resizable'])->checkPermission($abschnitt) ?>
            <label><?= _('Kreditpunkte') ?>
                <input <?= $perm->disable('kp') ?>
                        type="text" name="kp" id="kp"
                        value="<?= htmlReady($abschnitt->kp) ?>" size="5" maxlength="10">
            </label>
            <label><?= _('Zwischenüberschrift') ?>
                <?= MvvI18N::input('ueberschrift', $abschnitt->ueberschrift, ['maxlength' => '254'])->checkPermission($abschnitt) ?>
            </label>
    </fieldset>
    <input type="hidden" name="version_id" value="<?= $this->version->id ?>">
    <footer data-dialog-button>
        <? if ($abschnitt->isNew()) : ?>
            <?= Button::createAccept(_('Anlegen'), 'store', ['title' => _('Abschnitt anlegen')]) ?>
        <? else : ?>
            <?= Button::createAccept(_('Übernehmen'), 'store', ['title' => _('Änderungen übernehmen')]) ?>
        <? endif; ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $cancel_url, ['title' => _('zurück zur Übersicht')]) ?>
    </footer>
</form>
