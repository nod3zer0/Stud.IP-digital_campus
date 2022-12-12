<?php
/**
 * ResponsiveHelper.php
 *
 * This class collects helper methods for Stud.IP's responsive design.
 *
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license   GPL2 or any later version
 * @copyright Stud.IP core group
 * @since     Stud.IP 3.2
 */
class ResponsiveHelper
{
    /**
     * Returns the current navigation as an array.
     *
     * @return Array containing the navigation
     */
    public static function getNavigationArray()
    {
        $navigation = [];
        $activated  = [];

        $link_params = array_fill_keys(array_keys(URLHelper::getLinkParams()), null);

        foreach (Navigation::getItem('/')->getSubNavigation() as $path => $nav) {
            $image = $nav->getImage();

            $forceVisibility = false;
            /*
             * Special treatment for "browse" navigation which is normally hidden
             * when we are inside a course.
             */
            if ($path === 'browse' && !$image) {
                $image = Icon::create('seminar');
                $forceVisibility = true;
            }
            /*
             * Special treatment for "footer" navigation because
             * the real footer is hidden in responsive view.
             */
            if ($path === 'footer' && !$image) {
                $image = Icon::create('info');
                $nav->setTitle(_('Impressum & Information'));
                $forceVisibility = true;
            }

            $image_src = $image ? $image->copyWithRole('info_alt')->asImagePath() : false;
            $item = [
                'icon'     => $image_src ? self::getAssetsURL($image_src) : false,
                'title'    => (string) $nav->getTitle(),
                'url'      => URLHelper::getURL($nav->getURL(), $link_params, true),
                'parent'   => '/',
                'path'     => $path,
                'visible'  => $forceVisibility ? true : $nav->isVisible(true),
                'active'   => $nav->isActive()
            ];

            if ($nav->isActive()) {
                // course navigation is integrated in course sub-navigation items
                if ($path === 'course') {
                    $activated[] = 'browse/my_courses/' . (Context::get()->getId());
                } else {
                    $activated[] = $path;
                }
            }

            if ($nav->getSubnavigation() && $path != 'start') {
                $item['children'] = self::getChildren($nav, $path, $activated);
            }

            if ($path !== 'course') {
                $navigation[$path] = $item;
            }
        }

        return [$navigation, $activated];
    }

    /**
     * Recursively build a navigation array from the subnavigation/children
     * of a navigation object.
     *
     * @param Navigation  $navigation The navigation object
     * @param String      $path       Current path segment
     * @param array       $activated  Activated items
     * @param String|null $cid       Optional context ID
     * @return Array containing the children (+ grandchildren...)
     */
    protected static function getChildren(Navigation $navigation, $path, &$activated = [], string $cid = null)
    {
        $children = [];

        foreach ($navigation->getSubNavigation() as $subpath => $subnav) {
            /*if (!$subnav->isVisible()) {
                continue;
            }*/

            $originalSubpath = $subpath;
            $subpath = "{$path}/{$subpath}";

            $item = [
                'title'   => (string) $subnav->getTitle(),
                'url'     => URLHelper::getURL($subnav->getURL(), $cid ? ['cid' => $cid] : []),
                'parent'  => $path,
                'path'    => $subpath,
                'visible' => $subnav->isVisible(),
                'active'  => $subnav->isActive()
            ];

            if ($subnav->isActive()) {
                // course navigation is integrated in course sub-navigation items
                if ($path === 'course') {
                    $activated[] = 'browse/my_courses/' . Context::get()->getId() . '/' . $originalSubpath;
                } else {
                    $activated[] = $subpath;
                }
            }

            if ($subnav->getSubNavigation()) {
                $item['children'] = self::getChildren($subnav, $subpath);
            }

            if ($subpath === 'browse/my_courses') {
                $item['children'] = array_merge($item['children'] ?? [], static::getMyCoursesNavigation($activated));
            }

            $children[$subpath] = $item;
        }

        return $children;
    }

    /**
     * Try to get a compressed version of the passed navigation url.
     * The URL is processed is processed by URLHelper and the absolute uri
     * of the Stud.IP installation is stripped from it afterwards.
     *
     * @param  String $url The url to compress
     * @return String containing the compressed url
     */
    protected static function getURL($url, $params = [])
    {
        return str_replace($GLOBALS['ABSOLUTE_URI_STUDIP'], '', URLHelper::getURL($url, $params));
    }

    /**
     * Try to get a compressed version of the passed assets url.
     * The absolute uri of the Stud.IP installation is stripped from the url.
     *
     * @param  String $url The assets url to compress
     * @return String containing the compressed assets url
     */
    protected static function getAssetsURL($url)
    {
        return str_replace($GLOBALS['ASSETS_URL'], '', $url);
    }

    /**
     * Specialty for responsive navigation: build navigation items
     * for my courses in current semester.
     *
     * @return array
     */
    protected static function getMyCoursesNavigation($activated): array
    {
        if (!$GLOBALS['perm']->have_perm('admin')) {
            $sem_data = Semester::getAllAsArray();

            $currentIndex = -1;

            foreach ($sem_data as $index => $semester) {
                if ($semester['current']) {
                    $currentIndex = $index;
                    break;
                }
            }

            $params = [
                'deputies_enabled' => Config::get()->DEPUTIES_ENABLE
            ];

            $courses = MyRealmModel::getCourses($currentIndex, $currentIndex, $params);
        } else {
            $courses = [];
        }

        $items = [];

        $standardIcon = Icon::create('seminar', Icon::ROLE_INFO_ALT)->asImagePath();

        // Add current course to list.
        if (Context::get() && Context::isCourse()) {
            $courses[] = Context::get();
        }

        foreach ($courses as $course) {
            $avatar = CourseAvatar::getAvatar($course->id);
            if ($avatar->is_customized()) {
                $icon = $avatar->getURL(Avatar::SMALL);
            } else {
                $icon = $standardIcon;
            }

            $cnav = [
                'icon'     => $icon,
                'title'    => $course->getFullname(),
                'url'      => URLHelper::getURL('dispatch.php/course/details', ['cid' => $course->id]),
                'parent'   => 'browse/my_courses',
                'path'     => 'browse/my_courses/' . $course->id,
                'visible'  => true,
                'active'   => Course::findCurrent() ? Course::findCurrent()->id === $course->id : false,
                'children' => []
            ];

            foreach ($course->tools as $tool) {
                if (Seminar_Perm::get()->have_studip_perm($tool->getVisibilityPermission(), $course->id)) {

                    $path = 'browse/my_courses/' . $course->id;

                    $studip_module = $tool->getStudipModule();
                    if ($studip_module instanceof StudipModule) {
                        $tool_nav = $studip_module->getTabNavigation($course->id) ?: [];
                        foreach ($tool_nav as $nav_name => $navigation) {
                            if ($nav_name && is_a($navigation, 'Navigation')) {
                                $cnav['children'][$path . '/' . $nav_name] = [
                                    'icon'     => $navigation->getImage() ? $navigation->getImage()->asImagePath() : '',
                                    'title'    => $tool->getDisplayname(),
                                    'url'      => URLHelper::getURL($navigation->getURL(), ['cid' => $course->id]),
                                    'parent'   => 'browse/my_courses/' . $course->id,
                                    'path'     => 'browse/my_courses/' . $course->id . '/' . $nav_name,
                                    'visible'  => true,
                                    'active'   => $navigation->isActive(),
                                    'children' => static::getChildren(
                                        $navigation,
                                        'browse/my_courses/' . $course->id . '/' . $nav_name,
                                        $activated,
                                        $course->id
                                    ),
                                ];
                            }
                        }
                    }
                }
            }

            $items['browse/my_courses/' . $course->id] = $cnav;

        }

        return $items;
    }
}
