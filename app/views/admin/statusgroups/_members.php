<?php
/**
 * @var Admin_StatusgroupsController $controller
 * @var Statusgruppen $group
 * @var string $tutor
 */
?>
<? foreach ($group->members as $user): ?>
    <tr data-userid="<?= $user->user_id ?>">
        <td <?= ($tutor ? 'class="drag-handle"' : '') ?>></td>
        <td><?= $user->position + 1 ?></td>
        <td><?= $user->avatar() ?></td>
        <td><?= htmlReady($user->name()) ?></td>
        <td class="actions">
            <? $actionMenu = ActionMenu::get()->setContext($user->user) ?>
            <? $actionMenu->addLink($controller->url_for('settings/statusgruppen/', ['open' => $group->id, 'type' => 'role', 'username' => $user->user->username]),
                    _('Benutzer in dieser Rolle bearbeiten'),
                    Icon::create('edit', 'clickable')) ?>
            <? if ($tutor) : ?>
                <? $actionMenu->addLink($controller->url_for('admin/statusgroups/delete/' . $group->id . '/' . $user->user_id),
                        _('Person aus Gruppe austragen'),
                        Icon::create('trash', 'clickable'),
                        ['data-dialog' => 'size=auto']) ?>
            <? endif ?>
            <?= $actionMenu->render() ?>
        </td>
    </tr>
<? endforeach; ?>
