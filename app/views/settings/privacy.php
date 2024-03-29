<?
# Lifter010: TODO
use Studip\Button, Studip\LinkButton;

?>
<form method="post" action="<?= $controller->url_for('settings/privacy/global') ?>" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <fieldset>
        <legend>
            <?= _('Privatsphäre') ?>:
            <?= _('Globale Einstellungen') ?>
        </legend>

        <label>
            <?= _('Globale Sichtbarkeit') ?>
            <?= tooltipIcon(_('Sie können wählen, ob Sie für andere NutzerInnen sichtbar sein '
                              . 'und alle Kommunikationsfunktionen von Stud.IP nutzen können '
                              . 'wollen, oder ob Sie unsichtbar sein möchten und dann nur '
                              . 'eingeschränkte Kommunikationsfunktionen nutzen können.')) ?>
            <div>
            <? if (!in_array($global_visibility, ['always', 'never'])
                   && ($user_perm !== 'dozent' || !Config::get()->DOZENT_ALWAYS_VISIBLE)
            ):
                // only show selection if visibility can be changed
                ?>
                <select name="global_visibility" aria-describedby="global_vis_description" id="global_vis">
                <?php
                    if (count($user_domains)) {
                        printf("<option %s value=\"global\">" . _('sichtbar für alle Nutzenden') . "</option>", $global_visibility === 'global' ? 'selected="selected"' : '');
                        $visible_text = _('sichtbar für eigene Nutzerdomäne');
                    } else {
                        $visible_text = _('sichtbar');
                    }
                    printf("<option %s value=\"yes\">" . $visible_text . "</option>", ($global_visibility == 'yes' || ($global_visibility === 'unknown' && Config::get()->USER_VISIBILITY_UNKNOWN)) ? 'selected' : '');
                    printf("<option %s value=\"no\">" . _("unsichtbar") . "</option>", ($global_visibility == 'no' || ($global_visibility === 'unknown' && !Config::get()->USER_VISIBILITY_UNKNOWN)) ? 'selected' : '');
                ?>
                </select>
            <? else: ?>
                <? if ($global_visibility === 'never'): ?>
                    <em><?= _('Sie sind unsichtbar (durch Administrator/in eingestellt).') ?></em>
                <? elseif ($user_perm == 'dozent' && Config::get()->DOZENT_ALWAYS_VISIBLE): ?>
                    <em><?= _('Sie haben Lehrendenrechte und sind daher immer global sichtbar.') ?></em>
                <? else: ?>
                    <em><?= _('Sie sind immer global sichtbar (durch Administrator/in eingestellt).') ?></em>
                <? endif; ?>
                <input type="hidden" name="global_visibility" value="<?= $global_visibility ?>">
            <? endif; ?>
            </div>
        </label>

        <? if (Visibility::allowExtendedSettings($user)): ?>
            <div>
                <?= _('Erweiterte Einstellungen') ?>
                <?= tooltipIcon(
                        _('Stellen Sie hier ein, in welchen Bereichen des Systems Sie erscheinen wollen.')
                        . (Visibility::isFieldHideableForUser('email', $user)
                                ? _('Wenn Sie hier Ihre E-Mail-Adresse verstecken, wird stattdessen die E-Mail-Adresse Ihrer (Standard-)Einrichtung angezeigt.')
                                : '')
                ) ?>

            <? if (Visibility::isFieldHideableForUser('online', $user)): ?>
                <label>
                    <input type="checkbox" name="online" value="1"
                            <? if ($online_visibility) echo 'checked'; ?>>
                    <?= _('sichtbar in "Wer ist online" mit Zeitpunkt der letzten Aktivität') ?>
                </label>
            <? endif; ?>
            <? if (Visibility::isFieldHideableForUser('search', $user)): ?>
                <label>
                    <input type="checkbox" name="search" value="1"
                            <? if ($search_visibility) echo 'checked'; ?>>
                    <?= _('auffindbar über die Personensuche') ?>
                </label>
            <? endif; ?>
            <? if (Visibility::isFieldHideableForUser('email', $user)): ?>
                <label>
                    <input type="checkbox" name="email" value="1"
                            <? if ($email_visibility) echo 'checked'; ?>>
                    <?= _('eigene E-Mail Adresse sichtbar') ?>
                </label>
            <? endif; ?>
            </div>
        <? endif; ?>
    </fieldset>
    <footer>
        <?= Button::create(_('Übernehmen'), 'store', ['title' => _('Änderungen speichern')]) ?>
    </footer>
</form>

<div style="margin-top: 50px;"></div>

<form method="post" action="<?= $controller->url_for('settings/privacy/homepage') ?>" class="default">
    <?= CSRFProtection::tokenTag() ?>
    <input type="hidden" name="studipticket" value="<?= get_ticket() ?>">

    <fieldset>
        <legend>
            <?= _('Privatsphäre') ?>:
            <?= _('Eigenes Profil') ?>
        </legend>

        <table class="default settings-privacy">
            <thead>
                <tr>
                    <th><?= _('Profil-Element'); ?></th>
                    <th class="hidden-tiny-down" style='text-align: center;' colspan="<?= $colCount++ ?>"><?= _('sichtbar für'); ?></th>
                </tr>

                <tr class="hidden-tiny-down">
                    <th style="background: white; width: 34%;">&nbsp;</th>
                    <? foreach ($visibilities as $visibility): ?>
                        <th style="background: white; width: <?= floor(66 / $colCount) ?>%;"><?= htmlReady($visibility) ?></th>
                    <? endforeach; ?>
                </tr>
            </thead>
            <tbody class="privacy">
                <? foreach ($homepage_elements['entry'] as $element): ?>
                    <? if ($element['is_header']): ?>
                        <tr class="visibility-homepage-elements-header">
                            <th colspan="<?= 1 + $colCount ?>">
                                <?= htmlReady($element['name']) ?>
                            </th>
                        </tr>
                    <? else: ?>
                        <tr>
                            <td class="visibility-homepage-element" style="padding-left: <?= $element['padding'] ?>">
                                <span class="visibility-homepage-element-name"><?= htmlReady($element['name']) ?></span>
                                <span class="hidden-small-up"><?= _('sichtbar für') ?></span>
                            </td>
                            <? if ($element['is_category']): ?>
                                <td colspan="<?= $colCount ?>"></td>
                            <? else: ?>
                                <? foreach ($homepage_elements['states'] as $index => $state): ?>
                                    <td>
                                        <label style="white-space: nowrap;">
                                            <input type="radio"
                                                   name="visibility_update[<?= $element['id'] ?>]"
                                                   value="<?= $state ?>"
                                                   <? if ($element['state'] == $state) echo 'checked'; ?>>
                                            <span class="hidden-small-up"><?=  $visibilities[$index + 1] ?></span>
                                        </label>
                                    </td>
                                <? endforeach; ?>
                            <? endif; ?>
                        </tr>
                    <? endif; ?>
                <? endforeach; ?>
            </tbody>
        </table>
        <label>
            <?= _('Neue Elemente') ?>
            <select name="default">
                <option value="">-- <?= _('Bitte wählen') ?> --</option>
                <? foreach ($visibilities as $visibility => $label): ?>
                    <option value="<?= $visibility ?>" <? if ($default_homepage_visibility == $visibility) echo 'selected'; ?>>
                        <?= htmlReady($label) ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>

        <label>
            <?= _('Jetzt alle Sichtbarkeiten auf') ?>
            <select name="all">
                <option value="">-- <?= _('Bitte wählen') ?> --</option>
                <? foreach ($visibilities as $visibility => $label): ?>
                    <option value="<?= $visibility ?>">
                        <?= htmlReady($label) ?>
                    </option>
                <? endforeach; ?>
            </select>
        </label>
    </fieldset>

    <footer>
        <?= Button::create(_('Übernehmen'), 'store', ['title' => _('Änderungen speichern')]) ?>
    </footer>
</form>
