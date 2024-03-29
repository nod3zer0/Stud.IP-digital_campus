<?php
/**
 * files.php - controller to display files in a course
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Moritz Strohm <strohm@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       4.0
 */


class Institute_FilesController extends AuthenticatedController
{
    protected $allow_nobody = true;

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        checkObject();
        $this->studip_module = checkObjectModule('documents');
        if (!Context::isInstitute()) {
            throw new CheckObjectException(_('Es wurde keine passende Einrichtung gefunden.'));
        }
        $this->institute = Context::get();
        object_set_visit_module($this->studip_module->getPluginId());

        PageLayout::setHelpKeyword("Basis.Dateien");
        PageLayout::setTitle($this->institute->getFullname() . " - " . _("Dateien"));

        $this->last_visitdate = object_get_visit($this->institute->id, $this->studip_module->getPluginId());
        Navigation::activateItem('/course/files');

        if (is_object($GLOBALS['user']) && $GLOBALS['user']->id !== 'nobody') {
            $constraints = FileManager::getUploadTypeConfig($this->institute->id);

            PageLayout::addHeadElement('script', ['type' => 'text/javascript'], sprintf(
                'STUDIP.Files.setUploadConstraints(%s);',
                json_encode($constraints)
            ));
        }
    }

    private function buildSidebar()
    {
        $sidebar = Sidebar::get();

        $actions = new ActionsWidget();

        if ($this->topFolder->isEditable($GLOBALS['user']->id) && $this->topFolder->parent_id) {
            $actions->addLink(
                _("Ordner bearbeiten"),
                $this->url_for("file/edit_folder/".$this->topFolder->getId()),
                Icon::create("edit", "clickable"),
                ['data-dialog' => 1]
            );
        }

        if ($this->topFolder && $this->topFolder->isSubfolderAllowed($GLOBALS['user']->id)) {
            $actions->addLink(
                _('Neuer Ordner'),
                URLHelper::getUrl(
                    'dispatch.php/file/new_folder/' . $this->topFolder ->getId()
                ),
                Icon::create('folder-empty', 'clickable')
            )->asDialog();
        }
        if ($this->topFolder && $this->topFolder->isWritable($GLOBALS['user']->id)) {
            $actions->addLink(
                _('Dokument hinzufügen'),
                '#',
                Icon::create('add', 'clickable'),
                ['onclick' => "STUDIP.Files.openAddFilesWindow(); return false;"]
            );
        }

        $sidebar->addWidget($actions);

        $views = new ViewsWidget();
        $views->addLink(
            _('Ordneransicht'),
            $this->url_for('institute/files/index'),
            null,
            [],
            'index'
        )->setActive(true);
        $views->addLink(
            _('Alle Dateien'),
            $this->url_for('institute/files/flat'),
            null,
            [],
            'flat'
        );

        $sidebar->addWidget($views);
    }

    /**
     * Displays the files in tree view
     **/
    public function index_action($topFolderId = '')
    {
        $this->marked_element_ids = [];

        if (!$topFolderId) {
            $folder = Folder::findTopFolder($this->institute->id);
        } else {
            $folder = Folder::find($topFolderId);
        }

        if (!$folder) {
            PageLayout::postError(_('Der gewählte Ordner wurde nicht gefunden.'));
            $this->relocate($this->indexURL());
            return;
        }

        $this->topFolder = $folder->getTypedFolder();

        if (!$this->topFolder->isVisible($GLOBALS['user']->id) || $this->topFolder->range_id !== $this->institute->id) {
            throw new AccessDeniedException();
        }

        $this->buildSidebar();

        $this->render_template('files/index.php', $this->layout);
    }

    /**
     * Displays the files in flat view
     **/
    public function flat_action()
    {
        $this->marked_element_ids = [];

        $folder = Folder::findTopFolder($this->institute->id);

        if (!$folder) {
            throw new Exception(_('Fehler beim Laden des Hauptordners!'));
        }

        $this->topFolder = $folder->getTypedFolder();

        //find all files in all subdirectories:
        list($this->files, $this->folders) = array_values(FileManager::getFolderFilesRecursive($this->topFolder, $GLOBALS['user']->id));

        $this->table_title = '';
        $this->pagination_html = '';
        $this->range_type = 'institute';
        $this->show_default_sidebar = true;
        $this->enable_table_filter = true;
        $this->form_action = $this->url_for('file/bulk/' . $folder->getId());
        $this->render_template('files/flat.php', $this->layout);
    }
}
