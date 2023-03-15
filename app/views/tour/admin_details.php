<?php
/**
 * @var string $delete_question
 * @var TourController $controller
 * @var HelpTour $tour
 * @var HelpTourAudience $audience
 * @var string $tour_startpage
 * @var string $tour_id
 */

use Studip\Button, Studip\LinkButton;
?>

<?= $delete_question ?>

<form class="default" action="<?= $controller->url_for('tour/save/' . $tour->tour_id) ?>" method="post">
    <?= CSRFProtection::tokenTag(); ?>
    <fieldset>
        <legend><?= _('Grunddaten') ?></legend>

    <? if (!count($tour->steps)) : ?>
        <label>
           <span class="required">
                <?= _('Sprache der Tour:') ?>
           </span>
            <select name="tour_language">
            <? foreach ($GLOBALS['INSTALLED_LANGUAGES'] as $key => $language) : ?>
                <option value="<?= mb_substr($key, 0, 2) ?>" <? if ($tour->language === mb_substr($key, 0, 2)) echo 'selected'; ?>>
                    <?= htmlReady($language['name']) ?>
                </option>
            <? endforeach ?>
            </select>
        </label>
    <? endif ?>

        <label>
            <span class="required"><?= _('Name der Tour') ?>:</span>
            <input type="text" size="60" maxlength="255" name="tour_name"
                   value="<?= $tour ? htmlReady($tour->name) : '' ?>"
                   required="required" aria-required="true"
                   placeholder="<?= _('Bitte geben Sie einen Namen für die Tour an') ?>">
        </label>

        <label>
            <span class="required"> <?= _('Bemerkung') ?>:</span>
            <textarea cols="60" rows="5" name="tour_description"
                      required="required" aria-required="true"
                      placeholder="<?= _('Bitte geben an, welchen Inhalt die Tour hat') ?>"><?= $tour ? htmlReady($tour->description) : '' ?></textarea>
        </label>

        <label>
            <?= _('Art der Tour') ?>:
            <select name="tour_type">
                <option value="tour" <? if ($tour->type === 'tour') echo 'selected'; ?>>
                    <?= _('Tour (passiv)') ?>
                </option>
                <option value="wizard" <? if ($tour->type === 'wizard') echo 'selected'; ?>>
                    <?= _('Wizard (interaktiv)') ?>
                </option>
            </select>
        </label>

        <label>
            <?= _('Zugang zur Tour') ?>:
            <select name="tour_access">
                <option value="link" <? if (isset($tour->settings) && $tour->settings->access === 'link') echo 'selected'; ?>>
                    <?= _('unsichtbar') ?>
                </option>
                <option value="standard" <? if (isset($tour->settings) && $tour->settings->access === 'standard') echo 'selected'; ?>>
                    <?= _('Anzeige im Hilfecenter') ?>
                </option>
                <option value="autostart" <? if (isset($tour->settings) && $tour->settings->access === 'autostart') echo 'selected'; ?>>
                    <?= _('Startet bei jedem Aufruf der Seite, bis die Tour abgeschlossen wurde') ?>
                </option>
                <option value="autostart_once" <? if (isset($tour->settings) && $tour->settings->access === 'autostart_once') echo 'selected'; ?>>
                    <?= _('Startet nur beim ersten Aufruf der Seite') ?>
                </option>
            </select>
        </label>

    <? if (!count($tour->steps)) : ?>
        <label>
            <span class="required"><?= _('Startseite der Tour') ?>:</span>
            <input type="text" size="60" maxlength="255" name="tour_startpage"
                   value="<?= $tour_startpage ? htmlReady($tour_startpage) : '' ?>"
                   required="required" aria-required="true"
                   placeholder="<?= _('Bitte geben Sie eine Startseite für die Tour an') ?>"/>
        </label>

    <? endif ?>

        <section>
            <?= _('Geltungsbereich (Nutzendenstatus)') ?>:
            <? foreach (['autor', 'tutor', 'dozent', 'admin', 'root'] as $role) : ?>
            <label>
                <input type="checkbox" name="tour_roles[]" value="<?= $role ?>"
                       <? if (mb_strpos($tour->roles, $role) !== false) echo 'checked'; ?>>
                <?= $role ?>
            </label>
            <? endforeach ?>
        </section>
    </fieldset>
    <footer>
        <?= CSRFProtection::tokenTag() ?>
        <?= Button::createAccept(_('Speichern'), 'save_tour_details') ?>
        <?= LinkButton::createCancel(_('Abbrechen'), $controller->url_for('tour/admin_overview')) ?>
    </footer>
</form>

<? if (!$tour->isNew()) : ?>
    <form method="post">
        <?= CSRFProtection::tokenTag() ?>
        <table class="default sortable-table">
            <caption>
                <div class="step_list_title"><?= _('Schritte') ?></div>
            </caption>
            <thead>
                <tr>
                    <th data-sort="htmldata"><?= _('Nr.') ?></th>
                    <th data-sort="text"><?= _('Überschrift') ?></th>
                    <th><?= _('Inhalt') ?></th>
                    <th data-sort="text"><?= _('Seite') ?></th>
                    <th data-sort="htmldata"><?= _('Letzte Änderung') ?></th>
                    <th data-sort="htmldata"><?= _('Geändert von') ?></th>
                    <th class="actions"><?= _('Aktionen') ?></th>
                </tr>
            </thead>
            <tbody>
            <? if (count($tour->steps) > 0) : ?>
                <? foreach ($tour->steps as $step) : ?>
                    <tr id="<?= htmlReady("{$tour->id}_{$step->step}") ?>">
                        <td><?= $step->step ?></td>
                        <td><?= htmlReady($step->title) ?></td>
                        <td><?= htmlReady($step->tip) ?></td>
                        <td><?= htmlReady($step->route) ?></td>
                        <td><?= $tour->chdate ? date('d.m.Y H:i', $tour->chdate) : '' ?></td>
                        <td><?= htmlReady($step->author ? $step->author->getFullName() : ($step->author_email ?: _('unbekannt'))) ?></td>
                        <td class="actions">
                        <? $actionMenu = ActionMenu::get()->setContext($step->title) ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('tour/edit_step/' . $tour->tour_id . '/' . $step->step),
                            _('Schritt bearbeiten'),
                            Icon::create('edit'),
                            ['data-dialog' => 'size=auto;reload-on-close']
                        ) ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('tour/admin_details/' . $tour->tour_id, ['delete_tour_step' => $step->step]),
                            _('Schritt löschen'),
                            Icon::create('trash')
                        ) ?>
                        <? $actionMenu->addLink(
                            $controller->url_for('tour/edit_step/' . $tour->tour_id . '/' . ($step->step + 1) . '/new'),
                            _('Neuen Schritt hinzufügen'),
                            Icon::create('add'),
                            ['data-dialog' => 'size=auto;reload-on-close']
                        ) ?>
                            <?= $actionMenu->render() ?>
                        </td>
                    </tr>
                <? endforeach ?>
            <? else : ?>
                <tr>
                    <td colspan="7">
                        <?= _('In dieser Tour sind bisher keine Schritte vorhanden.') ?>
                    </td>
                </tr>
            <? endif ?>
            </tbody>
        </table>
    </form>
<? endif ?>
