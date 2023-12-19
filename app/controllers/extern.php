<?php
/**
 * extern.php - Controller to provide content to external pages.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       5.4
 */

class ExternController extends StudipController
{
    protected $with_session = true;

    /**
     * Action shows rendered external page.
     *
     * @param string $config_id The id of the configuration of the external page to show.
     * @throws Trails_DoubleRenderError
     */
    public function index_action(string $config_id)
    {
        $config = ExternPageConfig::find($config_id);
        if (!$config) {
            $this->render_text(
                Config::get()->EXTERN_PAGES_ERROR_MESSAGE
            );
            return;
        }
        try {
            $page = ExternPage::get($config);
            $page->setRequestParams();
        } catch (Exception $e) {
            $this->render_text(
                Config::get()->EXTERN_PAGES_ERROR_MESSAGE . '<br>' . $e->getMessage()
            );
            return;
        }

        $this->render_text($page->toString());
    }

}
