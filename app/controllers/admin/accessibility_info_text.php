<?php
/**
 * accessibility_info_text.php - controller class for administrating additional information text to accessible files
 * in file upload/edit dialogs
 *
 * @author    Michaela Brückner <brueckner@data-quest.de>
 * @license   GPL2 or any later version
 * @category  Stud.IP
 * @package   admin
 * @since     5.3
 */
class Admin_AccessibilityInfoTextController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        $GLOBALS['perm']->check('root');
        PageLayout::setTitle(_('Infotext zu barrierefreien Dateien'));
        Navigation::activateItem('/admin/locations/accessibility_info_text');
    }

    public function index_action()
    {
    }

    public function edit_action()
    {
        CSRFProtection::verifyUnsafeRequest();

        Config::get()->store(
            'ACCESSIBILITY_INFO_TEXT',
            Studip\Markup::purifyHtml(Request::i18n('accessbility_info_text'))
        );

        PageLayout::postSuccess(_('Die Einstellungen wurden gespeichert.'));
        $this->relocate('admin/accessibility_info_text/index');
    }
}
