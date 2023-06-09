<?php
/**
 * @var MyCoursesController $controller
 * @var bool $studygroups
 * @var string $cid
 * @var array $groups
 * @var array $group_names
 * @var array $semesters
 * @var string $group_field
 * @var string $current_semester
 */
?>
<form method="post" action="<?= $controller->store_groups($studygroups) ?>" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <input type="hidden" name="cid" value="<?= htmlReady($cid) ?>">
    <table class="default collapsable">
        <caption><?= _('Gruppenzuordnung') ?></caption>
        <colgroup>
            <col>
        <? for ($i = 0; $i < 9; $i += 1): ?>
            <col style="width: 32px">
        <? endfor; ?>
        </colgroup>
        <thead>
            <tr>
                <th><?= _('Veranstaltung') ?></th>
                <th colspan="100%"><?= _('Gruppen/Farbe') ?></th>
            </tr>
        </thead>
        <? foreach ($groups as $group_id => $group_members): ?>
            <tbody <? if (isset($semesters[$group_id]['semester_id']) && $current_semester != $semesters[$group_id]['semester_id']) echo 'class="collapsed"'; ?>>
            <? if ($group_field !== 'not_grouped'): ?>

                <tr class="table_header header-row">
                    <th colspan="10" class="toggle-indicator">
                        <a class="toggler" href="#">
                            <? if (is_array($group_names[$group_id])): ?>
                                <?= htmlReady($group_names[$group_id][1] . ' > ' . $group_names[$group_id][0]) ?>
                            <? else: ?>
                                <?= htmlReady($group_names[$group_id]) ?>
                            <? endif; ?>
                        </a>
                    </th>
                </tr>
            <? endif; ?>
            <? foreach ($group_members as $member): ?>
                <tr>
                    <td>
                        <a href="<?= URLHelper::getLink('seminar_main.php?auswahl=' . $member['seminar_id']) ?>">
                            <?= htmlReady(Config::get()->IMPORTANT_SEMNUMBER ? $my_sem[$member['seminar_id']]['sem_nr'] : '') ?>
                            <?= htmlReady($my_sem[$member['seminar_id']]['name']) ?>
                        </a>
                        <? if (!$my_sem[$member['seminar_id']]['visible']): ?>
                            <?= _('(versteckt)') ?>
                        <? endif; ?>
                    </td>
                <? for ($i = 0; $i < 9; $i++): ?>
                    <td class="gruppe<?= $i ?> mycourses-group-selector" onclick="this.querySelector('input').checked = true;">
                        <input type="radio" name="gruppe[<?= $member['seminar_id'] ?>]" value="<?= $i ?>"
                               aria-label="<?= _('Zugeordnet zu Gruppe ') . ($i + 1) ?>"
                               id="course-group-<?= htmlReady($member['seminar_id']) ?>-<?= $i ?>"
                            <? if ($my_sem[$member['seminar_id']]['gruppe'] == $i) echo 'checked'; ?>>
                        <label for="course-group-<?= htmlReady($member['seminar_id']) ?>-<?= $i ?>">
                            <?= sprintf(_('Gruppe %u zuordnen'), $i + 1) ?>
                        </label>
                    </td>
                <? endfor; ?>
                </tr>
            <? endforeach; ?>
            </tbody>
        <? endforeach; ?>
    </table>

    <div align="center" data-dialog-button>
        <div class="button-group">
            <?= Studip\Button::createAccept(_('Speichern')) ?>
            <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->url_for('my_courses/groups')) ?>
        </div>
    </div>
</form>
