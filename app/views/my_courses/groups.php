<form method="post" action="<?= $controller->url_for('my_courses/store_groups/'.$studygroups) ?>" class="default">
    <?= CSRFProtection::tokenTag() ?>

    <input type="hidden" name="cid" value="<?= $cid ?>">
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
            <tbody class="<?= $current_semester != $semesters[$group_id]['semester_id'] ? 'collapsed' : ''?>">
            <? if ($group_field !== 'not_grouped'): ?>

                <tr class="table_header header-row">
                    <th colspan="10" class="toggle-indicator">
                        <a class="toggler">
                            <? if (is_array($group_names[$group_id])): ?>
                                <?= htmlReady(my_substr($group_names[$group_id][1] . ' > ' . $group_names[$group_id][0], 0, 70)) ?>
                            <? else: ?>
                                <?= htmlReady(my_substr($group_names[$group_id], 0, 70)) ?>
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
