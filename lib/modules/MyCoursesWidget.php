<?php
/**
 * My courses widget. Displays a user's courses on the start page.
 *
 * @author  Viktoria Wiebe <viktoria.wiebe@web.de>
 * @license GPL2 or any later version
 * @since   Stud.IP 5.3
 */

require_once 'app/controllers/my_courses.php';

class MyCoursesWidget extends CorePlugin implements PortalPlugin
{
    public function getPluginName()
    {
        return _('Meine Veranstaltungen');
    }

    public function getMetadata()
    {
        return [
            'description' => _('Dieses Widget zeigt eine Liste Ihrer Veranstaltungen an.')
        ];
    }

    public function getPortalTemplate()
    {
        // get the MyCoursesController in order to prepare the correct data for the overview
        $controller = app(MyCoursesController::class, ['dispatcher' => app(\Trails_Dispatcher::class)]);
        $data = $controller->getPortalWidgetData();

        // add the json data to the head so vue can grab it
        PageLayout::addHeadElement('script', [], 'STUDIP.MyCoursesData = ' . json_encode($data) . ';');

        return $GLOBALS['template_factory']->open('start/my_courses');
    }
}
