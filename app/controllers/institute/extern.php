<?php
/**
 * extern.php - administration controller for external pages for institutes
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @package     extern
 * @since       5.4
 */

require_once 'app/controllers/admin/extern.php';

class Institute_ExternController extends Admin_ExternController
{

    /**
     * @see PluginController::before_filter()
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!Institute::findCurrent()) {
            require_once 'lib/admin_search.inc.php';

            // TODO: We don't seem to need this since admin_search will stop the script
            PageLayout::postInfo(_('Sie müssen zunächst eine Einrichtung auswählen'));
            $this->redirect('institute/basicdata/index?list=TRUE');
            return;
        }
    }

    /**
     * Initialize the controller.
     */
    protected function init()
    {
        $this->range = Context::getId();
        $this->template_path = 'institute/extern/extern_config/';
        $nav = Navigation::getItem('admin/institute/external');
        if ($nav) {
            $nav->setActive(true);
        }

        $this->getSystemWideConfigTypes();
        $this->config_types['Persons'] = [
            'name'        => _('Personen'),
            'description' => _('Liste der Personen an einer Einrichtung'),
            'icon'        => 'persons2',
            'template'    => 'institute/extern/extern_config/persons',
        ];
        $this->config_types['Download'] = [
            'name'        => _('Dateien'),
            'description' => _('Liste der Dateien zum Download'),
            'icon'        => 'files',
            'template'    => 'institute/extern/extern_config/download',
        ];

        $this->fetchPlugins(false);

        PageLayout::setTitle(_('Externe Seiten (Einrichtung)'));
    }

    protected function checkPerm()
    {
        if (Context::getId() && !$GLOBALS['perm']->have_studip_perm('admin', Context::getId())) {
            throw new AccessDeniedException();
        }
    }

}
