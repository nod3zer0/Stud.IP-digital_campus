<? if (count($members)) : ?>
    <table class="default">
        <colgroup>
            <col width="20">
            <? if($is_tutor) : ?>
                <? $cols = $group->id !== 'nogroup' ? 5 : 4 ?>
                <? if (!$is_locked) : ?>
                    <col width="20">
                    <? $cols = 6 ?>
                <? endif ?>
                <col>
                <? if ($group->id !== 'nogroup'): ?>
                    <col width="15%">
                <? endif; ?>
                <col width="35%">
            <? else : ?>
                <col>
                <? $cols = 3 ?>
            <? endif ?>

            <col width="80">
        </colgroup>
        <thead>
            <tr class="sortable">
                <? if ($is_tutor && !$is_locked) : ?>
                    <th>
                        <input aria-label="<?= sprintf(_('Alle Mitglieder dieser Gruppe auswählen')) ?>"
                               type="checkbox" name="all" value="1"
                               data-proxyfor=":checkbox.groupmembers-<?= $group->id ?>"
                               data-activates=".memberactions-<?= $group->id ?> select,.memberactions-<?= $group->id ?> button">
                    </th>
                <? endif ?>
                <th></th>
                <th <?= ($sort_by == 'nachname' && $sort_group == $group->id) ?
                    sprintf('class="sort%s"', $order) : '' ?>>
                    <a href="<?= URLHelper::getLink('#' . $group->id,
                        [
                            'sortby' => 'nachname',
                            'sort_group' => $group->id,
                            'order' => $group->id && $sort_by == 'nachname' ?
                                ($order == 'desc' ? 'asc' : 'desc') : 'desc',
                            'contentbox_open' => $group->id
                        ]) ?>">
                        <?=_('Nachname, Vorname')?>
                    </a>
                </th>
                <? if ($is_tutor) :?>
                    <? if ($group->id !== 'nogroup'): ?>
                        <th <?= ($sort_by == 'mkdate' && $sort_group == $group->id) ? sprintf('class="sort%s"', $order) : '' ?>>
                            <a href="<?= URLHelper::getLink('#' . $group->id,
                                [
                                    'sortby' => 'mkdate',
                                    'sort_group' => $group->id,
                                    'order' => $group->id && $sort_by == 'mkdate' ?
                                        ($order == 'desc' ? 'asc' : 'desc') : 'desc',
                                    'contentbox_open' => $group->id
                                ]) ?>">
                                <?= _('Anmeldedatum') ?>
                            </a>
                        </th>
                    <? endif; ?>
                    <th>
                        <?= _('Studiengang') ?>
                    </th>
                <? endif ?>
                <th><?= _('Aktion') ?></th>
            </tr>
        </thead>
        <tbody>
        <? $i = 1; foreach ($members as $m) : ?>
            <?= $this->render_partial(
                'course/statusgroups/_member',
                ['m' => $m, 'i' => $i++, 'is_tutor' => $is_tutor, 'is_locked' => $is_locked]) ?>
        <? endforeach ?>
        </tbody>
        <tfoot>
        <tr>
            <? if ($is_tutor) : ?>
                <td colspan="<?= $cols ?>">
                    <? if (!$is_locked) : ?>
                        <div class="memberselect">
                            <label>
                                <input aria-label="<?= sprintf(_('Alle Mitglieder dieser Gruppe auswählen')) ?>"
                                       type="checkbox" name="all" value="1"
                                       data-proxyfor=":checkbox.groupmembers-<?= $group->id ?>"
                                       data-activates=".memberactions-<?= $group->id ?> select,.memberactions-<?= $group->id ?> button">
                                <?= _('Alle Mitglieder dieser Gruppe auswählen') ?>
                            </label>
                        </div>
                        <div class="memberactions memberactions-<?= $group->id ?>">
                            <label>
                                <select name="members_action[<?= $group->id ?>]"
                                        id="members-action-<?= $group->id ?>">
                                    <option value="move"><?= _('In andere Gruppe verschieben') ?></option>
                                    <option value="copy"><?= _('In andere Gruppe kopieren') ?></option>
                                <? if ($group->id != 'nogroup') : ?>
                                    <option value="delete"><?= _('Aus dieser Gruppe entfernen') ?></option>
                                <? endif ?>
                                    <option value="cancel"><?= _('Austragen aus der Veranstaltung') ?></option>
                                </select>
                            </label>
                            <input type="hidden" name="source" value="<?= $group->id ?>">
                            <?= Studip\Button::create(
                                _('Ausführen'),
                                "batch_members[{$group->id}]",
                                ['data-dialog' => 'size=auto']
                            ) ?>
                        </div>
                    <? endif ?>
                </td>
            <? elseif (!$is_tutor) : ?>
                <td colspan="<?= $cols ?>">
                    <?= sprintf(_('+ %u unsichtbare Personen'), $invisible) ?>
                </td>
            <? endif ?>
        </tr>
        </tfoot>
    </table>
<? elseif ($invisible > 0): ?>
    <div class="statusgroup-no-members">
        <?= sprintf(
            ngettext(
                'Diese Gruppe hat %d unsichtbares Mitglied.',
                'Diese Gruppe hat %d unsichtbare Mitglieder.',
                $invisible
            ),
            htmlReady($invisible)
        ) ?>
    </div>
<? else : ?>
    <div class="statusgroup-no-members">
        <?= _('Diese Gruppe hat keine Mitglieder.') ?>
    </div>
<? endif ?>
