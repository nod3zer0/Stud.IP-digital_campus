<?php
if (!$controllerpath) {
    $controllerpath = 'files/index';
    if ($topFolder->range_type !== 'user') {
        $controllerpath = $topFolder->range_type . '/' . $controllerpath;
    }
}
$is_readable = $folder->isReadable($GLOBALS['user']->id);
$owner = User::find($folder->user_id) ?: new User();

$permissions = [];
if ($is_readable) {
    $permissions[] = 'r';
}
if ($folder->isEditable($GLOBALS['user']->id)) {
    $permissions[] = 'w';
}
if ($folder->isReadable($GLOBALS['user']->id)) {
    $permissions[] = 'd';
}
?>

<tr id="row_folder_<?= $folder->id ?>" data-permissions="<?= implode($permissions) ?>">
    <td>
    <? if ($is_readable) : ?>
        <input type="checkbox"
               name="ids[]"
               value="<?= $folder->id ?>"
               <? if (in_array($folder->getId(), (array)$marked_element_ids)) echo 'checked'; ?>>
    <? endif?>
    </td>
    <td class="document-icon" data-sort-value="<?=crc32(get_class($folder))?>">
        <a href="<?= $controller->link_for($controllerpath . '/' . $folder->getId())  ?>">
            <?= $folder->getIcon('clickable')->asImg(26) ?>
        </a>
    </td>
    <td>
        <a href="<?= $controller->link_for($controllerpath . '/' . $folder->getId()) ?>">
            <?= htmlReady($folder->name) ?>
        </a>
    </td>
    <? // -number + file count => directories should be sorted apart from files ?>
    <td data-sort-value="-1000000" class="responsive-hidden"></td>
<? if ($show_downloads): ?>
    <td data-sort-value="-1000000" class="responsive-hidden"></td>
<? endif; ?>
    <td data-sort-value="<?= htmlReady($owner->getFullName('no_title_rev')) ?>" class="responsive-hidden">
    <? if ($owner->id !== $GLOBALS['user']->id) : ?>
        <a href="<?= URLHelper::getLink('dispatch.php/profile?username=' . $owner->username) ?>">
            <?= htmlReady($owner->getFullName('no_title_rev')) ?>
        </a>
    <? else: ?>
        <?= htmlReady($owner->getFullName('no_title_rev')) ?>
    <? endif; ?>
    </td>
    <td title="<?= strftime('%x %X', $folder->mkdate) ?>" data-sort-value="<?= $folder->mkdate ?>" class="responsive-hidden">
        <?= $folder->mkdate ? reltime($folder->mkdate) : "" ?>
    </td>
    <? foreach ($topFolder->getAdditionalColumns() as $index => $column_name) : ?>
        <td class="responsive-hidden"
            data-sort-value="<?= htmlReady($folder->getAdditionalColumnOrderWeigh($index)) ?>">
            <? $content = $folder->getContentForAdditionalColumn($index) ?>
            <? if ($content) : ?>
                <?= is_a($content, "Flexi_Template") ? $content->render() : $content ?>
            <? endif ?>
        </td>
    <? endforeach ?>
    <td class="actions">
    <?php
        $actionMenu = ActionMenu::get()->setContext($folder->name);
        $actionMenu->addLink(
            $controller->url_for('file/details/' . $folder->getId()),
            _('Info'),
            Icon::create('info-circle', 'clickable', ['size' => 20]),
            ['data-dialog' => '1']
        );
        if ($folder->isEditable($GLOBALS['user']->id)) {
            $actionMenu->addLink(
                $controller->url_for('file/edit_folder/' . $folder->getId()),
                _('Ordner bearbeiten'),
                Icon::create('edit', 'clickable', ['size' => 20]),
                ['data-dialog' => '1']
            );
        }
        if ($folder->isReadable($GLOBALS['user']->id) && $GLOBALS['user']->id !== 'nobody') {
            $actionMenu->addLink(
                $controller->url_for('file/download_folder/' . $folder->getId()),
                _('Ordner herunterladen'),
                Icon::create('download', 'clickable', ['size' => 20])
            );
        }
        if ($folder->isEditable($GLOBALS['user']->id)) {
           $actionMenu->addLink(
               $controller->url_for('file/choose_destination/move/' . $folder->getId(), ['isfolder' => 1]),
                _('Ordner verschieben'),
                Icon::create('arr_1right', 'clickable', ['size' => 20]),
                ['data-dialog' => 'size=auto']
            );
            $actionMenu->addLink(
                $controller->url_for('file/choose_destination/copy/' . $folder->getId(), ['isfolder' => 1]),
                _('Ordner kopieren'),
                Icon::create('clipboard', 'clickable', ['size' => 20]),
                ['data-dialog' => 'size=auto']
            );
            if (Feedback::isActivated() && Feedback::hasCreatePerm($course->id)) {
                $actionMenu->addLink(
                    $controller->url_for('course/feedback/create_form/' . $folder->getId() . '/Folder'),
                    _('Neues Feedback-Element'),
                    Icon::create('star', Icon::ROLE_CLICKABLE, ['size' => 20]),
                    ['data-dialog' => '1']
                );
            }
            $actionMenu->addLink(
                $controller->url_for('file/delete_folder/' . $folder->getId()),
                _('Ordner löschen'),
                Icon::create('trash', 'clickable', ['size' => 20]),
                ['onclick' => "return STUDIP.Dialog.confirmAsPost('" . sprintf(_('Soll der Ordner "%s" wirklich gelöscht werden?'), jsReady($folder->name)) . "', this.href);"]
            );
        }
    ?>
        <?= $actionMenu->render() ?>
    </td>
</tr>
