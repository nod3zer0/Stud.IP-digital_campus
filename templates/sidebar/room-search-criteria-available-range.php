<?php
/**
 * Template documentation:
 *
 * A special time range search criteria named $criteria must be passed
 * to this template and must contain the following indexes:
 * - $range: The time range search criteria.
 * - $semester: The semester selector search criteria.
 * - $day_of_week: The day of week selector search criteria.
 *
 * This criteria has the following structure:
 * [
 *     'name' => The criteria's internal name.
 *     'title' => The title of the criteria.
 *     'optional' => Whether this criteria is optional (true) or not.
 *     'enabled' => Whether this criteria is enabled (true) or not.
 *     'semester' => Data for the semester search criteria.
 *         This key must contain an array with the following structure:
 *         [
 *             'objects' => Semester SORM objects.
 *             'value' => The ID of the selected semester.
 *         ]
 *     'range' => Data for the time range search criteria.
 *         This key must contain the following array:
 *         [
 *             'begin' => The DateTime object representing the begin time.
 *             'end' => The DateTime object representing the end time.
 *         ]
 *     'day_of_week' => Data for the day of week search criteria.
 *         This key must contain the following array:
 *         [
 *             'options' => An array with the days of the week, where the
 *                 array keys are the day numbers and the values are the
 *                 displayed names of the days.
 *             'value' => The day number of the selected day.
 *         ]
 * ]
 */
?>
<li class="item">
    <label class="undecorated">
        <input type="checkbox" class="special-item-switch" value="1"
               name="<?= htmlReady($criteria['name'] . '_enabled') ?>"
            <?= $criteria['enabled'] ? 'checked' : '' ?>>
        <?= htmlReady($criteria['title']) ?>
    </label>

    <div class="special-item-content">
        <? if ($criteria['semester']): ?>
            <label>
                <?= _('Semester') ?>
                <select name="<?= htmlReady($criteria['name'] . '_semester_id') ?>">
                    <option value=""><?= _('Bitte wählen') ?></option>
                    <? if (is_array($semesters)) : ?>
                        <? foreach ($semesters as $semester): ?>
                            <option value="<?= htmlReady($semester->id) ?>"
                                <?= ($semester->id == $criteria['semester']['value']
                                    ? 'selected="selected"'
                                    : '') ?>
                                    data-begin="<?= htmlReady($semester->vorles_beginn) ?>"
                                    data-end="<?= htmlReady($semester->vorles_ende) ?>">
                                <?= htmlReady($semester->name) ?>
                            </option>
                        <? endforeach ?>
                    <? endif ?>
                </select>
            </label>
        <? endif ?>
        <? if ($criteria['range']): ?>
            <div class="range-input-container">
                <input type="text" aria-label="<?= _('Startdatum') ?>" title="<?= _('Startdatum') ?>"
                       id="<?= htmlReady($criteria['name']) ?>_begin_date"
                       name="<?= htmlReady($criteria['name']) ?>_begin_date"
                       value="<?= htmlReady($criteria['range']['begin']->format('d.m.Y')) ?>"
                       class="hasDatePicker" data-date-picker>
                <input type="text" data-time="yes" aria-label="<?= _('Startzeitpunkt') ?>" title="<?= _('Startzeitpunkt') ?>"
                       data-time-picker='{"<":"#<?= htmlReady($criteria['name']) ?>_end_time"}'
                       id="<?= htmlReady($criteria['name']) ?>_begin_time"
                       name="<?= htmlReady($criteria['name']) ?>_begin_time"
                       value="<?= htmlReady($criteria['range']['begin']->format('H:i')) ?>"
                       class="hasTimepicker">
            </div>
            <div class="range-input-container">
                <input type="text" aria-label="<?= _('Enddatum') ?>" title="<?= _('Enddatum') ?>"
                       data-date-picker='{">=":"#<?= htmlReady($criteria['name']) ?>_begin_date"}'
                       id="<?= htmlReady($criteria['name']) ?>_end_date"
                       name="<?= htmlReady($criteria['name']) ?>_end_date"
                       value="<?= htmlReady($criteria['range']['end']->format('d.m.Y')) ?>"
                       class="hasDatePicker">
                <input type="text" data-time="yes" aria-label="<?= _('Endzeitpunkt') ?>" title="<?= _('Endzeitpunkt') ?>"
                       data-time-picker='{">":"#<?= htmlReady($criteria['name']) ?>_begin_time"}'
                       id="<?= htmlReady($criteria['name']) ?>_end_time"
                       name="<?= htmlReady($criteria['name']) ?>_end_time"
                       value="<?= htmlReady($criteria['range']['end']->format('H:i')) ?>"
                       class="hasTimepicker">
            </div>
        <? endif ?>
        <? if ($criteria['day_of_week']): ?>
        <label>
            <?= _('Wochentag') ?>
            <select name="<?= htmlReady($criteria['name'] . '_day_of_week') ?>">
                <? if (is_array($criteria['day_of_week']['options'])): ?>
                    <option value=""><?= _('Bitte wählen') ?></option>
                    <? foreach ($criteria['day_of_week']['options'] as $value => $title): ?>
                        <option value="<?= htmlReady($value) ?>"
                            <?= ($value === (int)$criteria['day_of_week']['value']
                                ? 'selected="selected"'
                                : '') ?>>
                            <?= htmlReady($title) ?>
                        </option>
                    <? endforeach ?>
                <? endif ?>
            </select>
            <? endif ?>
        </label>
    </div>
</li>
