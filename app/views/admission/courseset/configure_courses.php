<?php
/**
 * @var Admission_CoursesetController $controller
 * @var string $set_id
 * @var bool $participant_restriction
 * @var Course[] $courses
 * @var int $count_distinct_members
 * @var int $count_multi_members
 */
?>
<form name="configure_courses" action="<?= $controller->url_for('admission/courseset/configure_courses/' . $set_id) ?>" method="post">
    <table class="default">
        <thead>
            <tr>
                <th><?= _('Name')?></th>
                <th><?= _('Lehrende')?></th>
                <th><?= _('versteckt')?></th>
                <th><?= _('vorläufige Anmeldung')?></th>
                <th><?= _('verbindliche Anmeldung')?></th>
                <? if ($participant_restriction) : ?>
                <th><?= _('max. Teilnehmende')?></th>
                <? endif ?>
                <th><?= _('Teilnehmende aktuell')?></th>
                <th><?= _('Anmeldungen')?></th>
                <? if ($participant_restriction) : ?>
                <th><?= _('Warteliste')?></th>
                <th><?= _('Plätze')?></th>
                <th><?= _('Nachrücken')?></th>
                <? endif ?>
            </tr>
        </thead>
    <tbody>
    <? foreach ($courses as $course) : ?>
    <? $editable = (!$GLOBALS['perm']->have_studip_perm('admin', $course->id) && (!Config::get()->ALLOW_DOZENT_COURSESET_ADMIN && !$GLOBALS['perm']->have_perm('dozent'))) ? 'disabled' : '' ?>
        <tr>
            <td><?= htmlReady(($course->veranstaltungsnummer ? $course->veranstaltungsnummer .'|' : '')
                    . $course->name
                    . ($course->cycles ? ' (' . join('; ', $course->cycles->toString()) . ')' : ''))?></td>
            <td><?= htmlReady(join(', ', $course->members->findBy('status','dozent')->orderBy('position')->limit(3)->pluck('Nachname')))?></td>
            <td><input <?=$editable?> type="checkbox" name="configure_courses_hidden[<?= $course->id?>]" value="1" <?= $course->visible ? '' : 'checked'?>></td>
            <td><input <?=$editable?> type="checkbox" name="configure_courses_prelim[<?= $course->id?>]" value="1" <?= $course->admission_prelim ? 'checked' : ''?>></td>
            <td><input <?=$editable?> type="checkbox" name="configure_courses_binding[<?= $course->id?>]" value="1" <?= $course->admission_binding ? 'checked' : ''?>></td>
            <? if ($participant_restriction) : ?>
            <td><input <?=$editable?> type="text" size="2" name="configure_courses_turnout[<?= $course->id?>]" value="<?= (int)$course->admission_turnout ?>"></td>
            <? endif ?>
            <td><?= $course->getNumParticipants() ?></td>
            <td><?= sprintf("%d / %d", $applications[$course->id]['c'] ?? 0 , $applications[$course->id]['h'] ?? 0) ?></td>
            <? if ($participant_restriction) : ?>
            <td style="white-space:nowrap">
                <input <?=$editable?> type="checkbox" name="configure_courses_disable_waitlist[<?= $course->id?>]" value="1" <?= $course->admission_disable_waitlist ? '' : 'checked' ?>
                    title="<?= htmlReady(sprintf(_('Warteliste für %s aktivieren'), $course->name)) ?>"
                    data-activates="#waitlist_move_<?= $course->id?>, #waitlist_max_<?= $course->id?>">
            </td>
            <td style="white-space:nowrap">
                <input <?=$editable?> id="waitlist_max_<?= $course->id?>"
                    type="text" size="2" name="configure_courses_waitlist_max[<?= $course->id?>]"
                    value="<?= $course->admission_waitlist_max ?: ''?>"
                    title="<?= htmlReady(sprintf(_('Anzahl der Plätze auf der Warteliste für %s'), $course->name)) ?>"
                     <?= $course->admission_disable_waitlist ? 'disabled' : ''?>>
            </td>
            <td style="white-space:nowrap">
                <input <?=$editable?> type="checkbox"
                    id="waitlist_move_<?= $course->id?>" <?= $course->admission_disable_waitlist ? 'disabled' : ''?>
                    name="admission_disable_waitlist_move[<?= $course->id?>]" value="1"
                    title="<?= htmlReady(sprintf(_('Aktivieren des automatischen Nachrückens aus der Warteliste für %s'), $course->name)) ?>"
                    <? if (!$course->admission_disable_waitlist_move) echo 'checked'; ?>>
            </td>
            <? endif ?>
        </tr>
    <? endforeach ?>
    </tbody>
</table>
<div>
    <?=_("Anzahl aller Teilnehmenden:")?> <?=$count_distinct_members?>
    <?  if ($count_distinct_members) : ?>
        <a href="<?= $controller->link_for('admission/courseset/configure_courses/' . $set_id .'/download_all_members')?>" title="<?= _("Download")?>">
            <?= Icon::create('file-office') ?>
        </a>
    <? endif ?>
</div>
<div>
    <?=_("Mehrfachteilnahmen:")?> <?=$count_multi_members?>
    <?  if ($count_multi_members) : ?>
        <a href="<?= $controller->link_for('admission/courseset/configure_courses/' . $set_id .'/download_multi_members')?>" title="<?= _("Download")?>">
            <?= Icon::create('file-office') ?>
        </a>
    <? endif ?>
</div>
<div data-dialog-button>
    <?= Studip\Button::create(_('Speichern'), 'configure_courses_save') ?>
    <?= Studip\LinkButton::create(_('Download'), $controller->url_for('admission/courseset/configure_courses/' . $set_id .'/csv')) ?>
</div>
<?= CSRFProtection::tokenTag()?>
</form>
<?
