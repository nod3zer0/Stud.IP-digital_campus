<?php
/**
 * @var WikiPage[] $pages
 * @var Course_WikiController $controller
 */
?>

<table class="default sortable-table" data-sortlist="[[0, 0]]">
    <caption>
        <?= _('Alle Seiten des Wikis') ?>
    </caption>
    <thead>
        <tr>
            <th data-sort="text"><?= _('Seitenname') ?></th>
            <th data-sort="digit"><?= _('Änderungen') ?></th>
            <th data-sort="htmldata"><?= _('Letzte Änderung') ?></th>
            <th data-sort="text"><?= _('Zuletzt bearbeitet von') ?></th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($pages as $page) : ?>
        <tr>
            <td data-text="<?= htmlReady($page->name) ?>">
                <a href="<?= $controller->page($page) ?>">
                    <?= htmlReady($page->name) ?>
                </a>
            </td>
            <td><?= count($page->versions) + 1 ?></td>
            <td data-sort-value="<?= $page->chdate ?>">
                <?= $page->chdate > 0 ? date('d.m.Y H:i:s', $page->chdate) : _('unbekannt') ?>
            </td>
            <td data-text="<?= htmlReady($page->user ? $page->user->getFullName() : _('unbekannt')) ?>">
                <?= Avatar::getAvatar($page->user_id)->getImageTag(Avatar::SMALL) ?>
                <?= htmlReady($page->user ? $page->user->getFullName() : _('unbekannt')) ?>
            </td>
        </tr>
        <? endforeach ?>
    </tbody>
</table>
