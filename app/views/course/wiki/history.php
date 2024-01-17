<?php
/**
 * @var WikiPage $page
 * @var Course_WikiController $controller
 */
?>

<table class="default sortable-table" data-sortlist="[[0, 1]]">
    <caption>
        <?= sprintf(_('%s - Versionshistorie'), htmlReady($page->name)) ?>
    </caption>
    <colgroup>
        <col style="width: 60px;">
        <col>
        <col>
        <col>
    </colgroup>
    <thead>
        <tr>
            <th data-sort="text"><?= _('Version') ?></th>
            <th data-sort="text"><?= _('Autor/in') ?></th>
            <th data-sort="text"><?= _('Erstellt am') ?></th>
            <th data-sort="false" class="actions"><?= _('Aktion') ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td data-sort-value="<?= count($page->versions) + 1 ?>">
                <a href="<?= $controller->page($page) ?>">
                    <?= count($page->versions) + 1 ?>
                </a>
            </td>
            <td data-sort-value="<?= htmlReady($page->user ? $page->user->getFullName() : _('unbekannt')) ?>">
                <? if ($page->user) : ?>
                <a href="<?= URLhelper::getLink('dispatch.php/profile', ['username' => $page->user->username]) ?>">
                <? endif ?>
                    <?= Avatar::getAvatar($page['user_id'])->getImageTag(Avatar::SMALL) ?>
                    <?= htmlReady($page->user ? $page->user->getFullName() : _('unbekannt')) ?>
                <? if ($page->user) : ?>
                </a>
                <? endif ?>
            </td>
            <td data-sort-value="<?= $page->chdate ?>"><?= $page->chdate > 0 ? date('d.m.Y H:i:s', $page->chdate) : _('unbekannt') ?></td>
            <td class="actions">
                <a href="<?= $controller->versiondiff($page) ?>" data-dialog>
                    <?= Icon::create('log')->asImg(['class' => 'text-bottom']) ?>
                </a>
            </td>
        </tr>
        <? foreach ($page->versions as $i => $version) : ?>
        <tr>
            <td>
                <a href="<?= $controller->version($version) ?>">
                    <?= count($page->versions) - $i ?>
                </a>
            </td>
            <td>
                <? if ($version->user) : ?>
                <a href="<?= URLhelper::getLink('dispatch.php/profile', ['username' => $version->user->username]) ?>">
                <? endif ?>
                    <?= Avatar::getAvatar($version['user_id'])->getImageTag(Avatar::SMALL) ?>
                    <?= htmlReady($version->user ? $version->user->getFullName() : _('unbekannt')) ?>
                <? if ($version->user) : ?>
                </a>
                <? endif ?>
            </td>
            <td><?= $version->mkdate > 0 ? date('d.m.Y H:i:s', $version->mkdate) : _('unbekannt') ?></td>
            <td class="actions">
                <a href="<?= $controller->versiondiff($page, $version->id) ?>" data-dialog>
                    <?= Icon::create('log')->asImg(['class' => 'text-bottom']) ?>
                </a>
            </td>
        </tr>
        <? endforeach ?>
    </tbody>
</table>
