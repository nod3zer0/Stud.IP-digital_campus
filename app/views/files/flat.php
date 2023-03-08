<?php
$show_downloads = in_array(Config::get()->DISPLAY_DOWNLOAD_COUNTER, ['always', 'flat']);
$vue_files = [];
foreach ($files as $file) {
    if ($file->isVisible($GLOBALS['user']->id)) {
        $vue_files[] = FilesystemVueDataManager::getFileVueData($file, $file->getFolderType(), $last_visitdate);
    }
}
$vue_files = array_values(SimpleCollection::createFromArray($vue_files)->orderBy('chdate desc')->toArray());

$topFolder = new StandardFolder();
$vue_topFolder = [
    'description' => $topFolder->getDescriptionTemplate(),
    'additionalColumns' => $topFolder->getAdditionalColumns(),
];
if (is_a($vue_topFolder['description'], "Flexi_Template")) {
    $vue_topFolder['description'] = $vue_topFolder['description']->render();
}
$vue_topFolder['buttons'] = '<span class="multibuttons">';
$vue_topFolder['buttons'] .= Studip\Button::create(_('Herunterladen'), 'download', [
    'data-activates-condition' => 'table.documents tr[data-permissions*=d] :checkbox:checked'
]);
if ($topFolder->isWritable($GLOBALS['user']->id)) {
    $vue_topFolder['buttons'] .= Studip\Button::create(_('Verschieben'), 'move', [
        'formaction'  => $controller->url_for('file/choose_destination/move/bulk'),
        'data-dialog' => 'size=auto',
        'data-activates-condition' => 'table.documents tr[data-permissions*=w] :checkbox:checked'
    ]);
}
$vue_topFolder['buttons'] .= Studip\Button::create(_('Kopieren'), 'copy', [
    'formaction'  => $controller->url_for('file/choose_destination/copy/bulk'),
    'data-dialog' => 'size=auto',
    'data-activates-condition' => 'table.documents tr[data-permissions*=r] :checkbox:checked'
]);
if ($topFolder->isWritable($GLOBALS['user']->id)) {
    $vue_topFolder['buttons'] .= Studip\Button::create(_('Löschen'), 'delete', [
        'data-confirm'             => _('Soll die Auswahl wirklich gelöscht werden?'),
        'data-activates-condition' => 'table.documents tr[data-permissions*=w] :checkbox:checked'
    ]);
}
$vue_topFolder['buttons'] .= '</span>';
foreach ($topFolder->getAdditionalActionButtons() as $button) {
    $vue_topFolder['buttons'] .= $button;
}
?>
<form id="files_table_form"
      method="post"
      action="<?= htmlReady($form_action ?? '') ?>"
      data-files="<?= htmlReady(json_encode($vue_files)) ?>"
      data-topfolder="<?= htmlReady(json_encode((array) $vue_topFolder)) ?>">
    <?= CSRFProtection::tokenTag() ?>
    <files-table :showdownloads="<?= $show_downloads ? "true" : "false" ?>"
                 :breadcrumbs="breadcrumbs"
                 :files="files"
                 :folders="folders"
                 :topfolder="topfolder"
                 :allow_filter="<?= json_encode(!empty($enable_table_filter)) ?>"
                 table_title="<?= htmlReady($table_title ?? '') ?>"
                 pagination="<?= htmlReady($pagination_html ?? '') ?>"
                 :initial_sort="{sortedBy:'chdate',sortDirection:'desc'}"
    ></files-table>
</form>
<?
if (!empty($show_default_sidebar)) {
    if (!empty($enable_table_filter)) {
        $widget = new SidebarWidget();
        $widget->setId('table-view-filter');
        $widget->setTitle(_('Filter'));
        $widget->addElement(new WidgetElement('<div></div>'));
        Sidebar::get()->addWidget($widget);
    }

    $views = new ViewsWidget();
    $views->addLink(
        _('Ordneransicht'),
        $controller->url_for((isset($range_type) ? $range_type . '/' : '') . 'files/index'),
        null,
        [],
        'index'
    );
    $views->addLink(
        _('Alle Dateien'),
        $controller->url_for((isset($range_type) ? $range_type . '/' : '') . 'files/flat'),
        null,
        [],
        'flat'
    )->setActive(true);
    Sidebar::get()->addWidget($views);
}
