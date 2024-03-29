<tbody style="vertical-align: top;">
<? if ($th_title): ?>
    <tr>
        <th colspan="<?= 1 + count($structure) - ($structure['actions'] ? 1 : 0) ?>">
            <?= htmlReady($th_title) ?>
        </th>
    <? if (!empty($structure['actions'])): ?>
        <th class="actions">
        <?= ActionMenu::get()
            ->setContext($th_title)
            ->condition($mail_status ?? false)
            ->addLink(
                $controller->url_for('messages/write?filter=inst_status', [
                    'who'             => $key ?? '',
                    'default_subject' => Context::get()->Name,
                    'inst_id'         => Context::getId(),
                ]),
                sprintf(_('Nachricht an alle Mitglieder mit dem Status %s verschicken'), $th_title),
                Icon::create('mail'),
                ['data-dialog' => '']
            )
            ->condition($mail_gruppe ?? false)
            ->addLink(
                $controller->url_for('messages/write', [
                    'group_id'        => $group->id ?? null,
                    'default_subject' => Context::get()->Name,
                ]),
                sprintf(_('Nachricht an alle Mitglieder der Gruppe %s verschicken'), $th_title),
                Icon::create('mail'),
                ['data-dialog' => '']
            ) ?>
        </th>
    <? endif; ?>
    </tr>
<? endif; ?>
<? $role_entries = [] ?>
<? foreach ($members as $member):
        $default_entries = DataFieldEntry::getDataFieldEntries([$member->user_id, $institute->id]);

        if (!empty($group)) {
            $role_entries = DataFieldEntry::getDataFieldEntries([$member->user_id, $group->id]);
        }
?>
    <tr>
        <td>
            <a href="<?= $controller->link_for('profile', ['username' => $member->username]) ?>">
                <?= Avatar::getAvatar($member->user_id, $member->username)->getImageTag(Avatar::SMALL) ?>
            </a>
        </td>
        <td>
        <? if ($admin_view): ?>
            <a href="<?= $controller->link_for("settings/statusgruppen#{$institute->id}", ['username' => $member->username, 'contentbox_open' => $institute->id]) ?>">
                <?= htmlReady($member->getUserFullname('full_rev')) ?>
            </a>
        <? else: ?>
            <a href="<?= $controller->link_for('profile', ['username' => $member->username]) ?>">
                <?= htmlReady($member->getUserFullname('full_rev')) ?>
            </a>
        <? endif; ?>
        </td>
    <? if (!empty($structure['status'])): ?>
        <td><?= htmlReady($member->inst_perms) ?></td>
    <? endif; ?>
    <? if (!empty($structure['statusgruppe'])): ?>
        <td></td>
    <? endif; ?>
    <? if (!empty($structure['raum'])): ?>
        <td><?= htmlReady($member->raum) ?></td>
    <? endif; ?>
    <? if (!empty($structure['sprechzeiten'])): ?>
        <td><?= htmlReady($member->sprechzeiten) ?></td>
    <? endif; ?>
    <? if (!empty($structure['telefon'])): ?>
        <td><?= htmlReady($member->Telefon) ?></td>
    <? endif; ?>
    <? if (!empty($structure['email'])): ?>
        <td><?= htmlReady(get_visible_email($member->user_id)) ?></td>
    <? endif; ?>
    <? if (!empty($structure['homepage'])): ?>
        <td><?= htmlReady($member->user_info->Home) ?></td>
    <? endif; ?>
    <? foreach (array_filter($datafields_list, function ($e) use ($structure) { return isset($structure[$e->getId()]); }) as $entry): ?>
        <td>
        <? if ($role_entries[$entry->getId()] && $role_entries[$entry->getId()]->getValue() !== 'default_value'): ?>
            <?= $role_entries[$entry->getId()]->getDisplayValue() ?>
        <? elseif ($default_entries[$entry->getId()]): ?>
            <?= $default_entries[$entry->getId()]->getDisplayValue() ?>
        <? endif; ?>
        </td>
    <? endforeach; ?>
    <? if (!empty($structure['actions'])): ?>
        <td class="actions">
        <?= ActionMenu::get()
            ->setContext($member->user)
            ->addLink(
                $controller->url_for("messages/write?rec_uname={$member->username}"),
                _('Nachricht an Benutzer verschicken'),
                Icon::create('mail'),
                ['data-dialog' => '']
            )
            ->conditionAll(
                $admin_view && !LockRules::Check($institute->id, 'participants') // General permission check
                && ($member->inst_perms !== 'admin' // Don't delete admins
                    || ($GLOBALS['perm']->get_profile_perm($member->user_id) === 'admin' // unless you are a global admin yourself
                        && $member->user_id !== $GLOBALS['user']->id)) // but don't delete yourself
            )
            ->condition(isset($group))
            ->addLink(
                $controller->url_for('institute/members/remove_from_group', (isset($group) ? $group->id : null), $type, ['username' => $member->username]),
                _('Person aus Gruppe austragen'),
                Icon::create('door-leave'),
                ['data-confirm' => _('Wollen Sie die Person wirklich aus der Gruppe austragen?')]
            )
            ->condition(!isset($group))
            ->addLink(
                $controller->url_for('institute/members/remove_from_institute', $type, ['username' => $member->username]),
                _('Person aus Einrichtung austragen'),
                Icon::create('door-leave'),
                ['data-confirm' => _('Wollen Sie die Person wirklich aus der Einrichtung austragen?')]
            ) ?>
        </td>
    <? endif; ?>
    </tr>
<? if (!empty($structure['statusgruppe'])): ?>
    <?
    $my_groups = $groups->filter(function ($group) use ($member) {
        return $group->isMember($member->user_id);
    });
    foreach ($my_groups as $group):
        $group_member = $group->members->findOneBy('user_id', $member->user_id);
    ?>
        <tr>
            <td colspan="<?= 2 + (int)!empty($structure['status']) ?>"></td>
            <td colspan="<?= 1 + count(array_filter(['raum', 'sprechzeiten', 'telefon', 'email', 'homepage'], function ($item) use ($structure) { return !empty($structure[$item]); })) ?>">
            <? if ($admin_view): ?>
                <a href="<?= $controller->link_for('admin/statusgroups/editGroup/' . $group->id) ?>">
            <? endif; ?>
                <?= htmlReady($group->getFullGenderedName($member->user_id)) ?>
            <? if ($admin_view): ?>
                </a>
            <? endif; ?>
            </td>
        <? foreach ($group_member->datafields->filter(function ($e) use ($dview) { return in_array($e->getId(), $dview); }) as $entry): ?>
            <td>
            <? if ($entry->getValue() === 'default_value'): ?>
                <?= $default_entries[$e_id]->getDisplayValue() ?>
            <? else: ?>
                <?= $entry->getDisplayValue() ?>
            <? endif; ?>
            </td>
        <? endforeach; ?>
        <? if (!empty($structure['actions'])): ?>
            <td class="actions">
            <?= ActionMenu::get()
                ->conditionAll($admin_view && !LockRules::Check($institute->id, 'participants'))
                ->addLink(
                    $controller->url_for("settings/statusgruppen#{$group->id}", [
                        'username'        => $member->username,
                        'contentbox_open' => $group->id,
                    ]),
                    _('Gruppendaten bearbeiten'),
                    Icon::create('edit')
                )
                ->addLink(
                    $controller->url_for('institute/members/remove_from_group', $group->id, $type, ['username' => $member->username]),
                    _('Person aus Gruppe austragen'),
                    Icon::create('door-leave'),
                    ['data-confirm' => _('Wollen Sie die Person wirklich aus der Gruppe austragen?')]
                ) ?>
            </td>
        <? endif; ?>
        </tr>
    <? endforeach; ?>
<? endif; ?>
<? endforeach; ?>
</tbody>
