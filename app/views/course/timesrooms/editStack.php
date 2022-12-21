<?php
/**
 * @var Course_TimesroomsController $controller
 * @var string $cycle_id
 * @var array $linkAttributes
 * @var array $checked_dates
 * @var array $selectable_rooms
 * @var array $tpl
 * @var QuickSearch $room_search
 * @var array $only_bookable_rooms
 * @var array $teachers
 * @var array $gruppen
 * @var int $preparation_time
 * @var int $max_preparation_time
 */
?>
<form method="post" action="<?= $controller->url_for('course/timesrooms/saveStack/' . $cycle_id, $linkAttributes) ?>"
      class="default collapsable" data-dialog="size=big">
    <?= CSRFProtection::tokenTag()?>
    <input type="hidden" name="method" value="edit">
    <input type="hidden" name="checked_dates" value="<?= implode(',', $checked_dates) ?>">

    <fieldset>
        <legend><?= _('Raumangaben') ?></legend>
        <? if (Config::get()->RESOURCES_ENABLE && (!empty($room_search) || !empty($selectable_rooms))): ?>
            <section>
                <label>
                    <input type="radio" name="action" value="room" id="room" data-activates="input.preparation-time[name='preparation_time']">
                    <?= _('Raum direkt buchen') ?>
                </label>
                <? if (!empty($room_search)) : ?>
                    <label>
                        <?= _('Raumsuche') ?>
                        <span class="flex-row"></span>
                        <?= $room_search
                            ->setAttributes(['onFocus' => "jQuery('input[type=radio][name=action][value=room]').prop('checked', true)"])
                            ->render() ?>
                        <? if (!$only_bookable_rooms) : ?>
                            <?= $this->render_partial('course/timesrooms/_bookable_rooms_icon.php') ?>
                        <? endif ?>
                    </label>
                <? else : ?>
                    <label>
                        <?= _('Raum auswählen') ?>
                        <span class="flex-row">
                                <select name="room_id" onFocus="jQuery('input[type=radio][name=action][value=room]').prop('checked', 'checked')">
                                    <option value="0"><?= _('Auswählen') ?></option>
                                    <? foreach ($selectable_rooms as $room): ?>
                                        <option value="<?= htmlReady($room->id)?>">
                                            <?= htmlReady($room->name) ?>
                                        </option>
                                    <? endforeach ?>
                                </select>
                                <? if (!$only_bookable_rooms) : ?>
                                    <?= $this->render_partial('course/timesrooms/_bookable_rooms_icon.php') ?>
                                <? endif ?>
                           </span>
                    </label>
                <? endif ?>
                <label>
                    <?= _('Rüstzeit (in Minuten)') ?>
                    <input type="number" name="preparation_time"
                           class="preparation-time"
                           value="<?= htmlReady($preparation_time) ?>"
                           min="0" max="<?= htmlReady($max_preparation_time) ?>">
                </label>
            </section>
            <? $placerholder = _('Freie Ortsangabe (keine Raumbuchung)') ?>
        <? else : ?>
            <? $placerholder = _('Freie Ortsangabe') ?>
        <? endif ?>
        <section>
            <label>
                <input type="radio" name="action" value="freetext" data-deactivates="input.preparation-time[name='preparation_time']">
                <?= $placerholder ?>
            </label>
            <label>
                <input type="text" name="freeRoomText" value="<?= htmlReady($tpl['freeRoomText']) ?>"
                       placeholder="<?= $placerholder ?>"
                       onFocus="jQuery('input[type=radio][name=action][value=freetext]').prop('checked', 'checked')">
            </label>
        </section>
        <? if (Config::get()->RESOURCES_ENABLE) : ?>
            <label>
                <input type="radio" name="action" value="noroom" data-deactivates="input.preparation-time[name='preparation_time']">
                <?= _('Kein Raum') ?>
            </label>
        <? endif ?>

        <label>
            <input type="radio" name="action" value="nochange" checked="checked" data-deactivates="input.preparation-time[name='preparation_time']">
            <?= _('Keine Änderungen an den Raumangaben vornehmen') ?>
        </label>
    </fieldset>

    <fieldset class="collapsed">
        <legend><?= _('Terminangaben') ?></legend>
        <label>
            <?= _('Art') ?>
            <select name="course_type" id="course_type">
                <option value=""><?= _('-- Keine Änderung --') ?></option>
                <? foreach ($GLOBALS['TERMIN_TYP'] as $id => $value) : ?>
                    <option value="<?= $id ?>"><?= htmlReady($value['name']) ?></option>
                <? endforeach ?>
            </select>
        </label>
    </fieldset>

    <fieldset class="collapsed">
        <legend><?= _('Durchführende Lehrende') ?></legend>
        <label>
            <?= _('Aktion auswählen') ?>
            <select name="related_persons_action" id="related_persons_action">
                <option value=""><?= _('Bitte wählen') ?></option>
                <option value="add"><?= _('Lehrende hinzufügen') ?></option>
                <option value="delete"><?= _('Lehrende entfernen') ?></option>
            </select>
        </label>

        <? if (!empty($teachers)) : ?>
            <label>
                <?= _('Lehrende') ?>
                <select name="related_persons[]" id="related_persons" multiple>
                    <? foreach ($teachers as $teacher) : ?>
                        <option value="<?= htmlReady($teacher['user_id']) ?>">
                            <?= htmlReady($teacher['fullname']) ?>
                        </option>
                    <? endforeach ?>
                </select>
            </label>
        <? endif ?>
    </fieldset>

    <? if (count($gruppen)) : ?>
        <fieldset class="collapsed">
            <legend><?= _('Beteiligte Gruppen') ?></legend>
            <label>
                <?= _('Aktion auswählen') ?>
                <select name="related_groups_action" id="related_groups_action">
                    <option value=""><?= _('Bitte wählen') ?></option>
                    <option value="add"><?= _('Gruppen hinzufügen') ?></option>
                    <option value="delete"><?= _('Gruppen entfernen') ?></option>
                </select>
            </label>

            <label>
                <?= _('Statusgruppen')?>
                <select id="related_groups" name="related_groups[]" multiple>
                    <? foreach ($gruppen as $gruppe) : ?>
                        <option value="<?= htmlReady($gruppe->statusgruppe_id) ?>"><?= htmlReady($gruppe->name) ?></option>
                    <? endforeach ?>
                </select>
            </label>
        </fieldset>
    <? endif ?>


    <footer data-dialog-button>
        <?= Studip\Button::createAccept(_('Änderungen speichern'), 'save') ?>
        <? if (Request::int('fromDialog')) : ?>
            <?= Studip\LinkButton::create(
                _('Zurück zur Übersicht'),
                $controller->url_for('course/timesrooms/index'),
                ['data-dialog' => 'size=big']
            ) ?>
        <? endif ?>
    </footer>
</form>
