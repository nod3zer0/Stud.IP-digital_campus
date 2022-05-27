<?php
# Lifter010: TODO
/**
 * SkipLinks.php - API for global skip links
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */

/**
 * The SkipLinks class provides utility functions to handle
 * the integration of skip links.
 */
class SkipLinks
{
    /**
     * array of Skip links
     * @var array
     */
    private static $links = [];

    /**
     * Returns whether skiplinks are enabled.
     *
     * @return boolean
     */
    public static function isEnabled()
    {
        if (isset($GLOBALS['user']->id)) {
            //Use the user configuration:
            return UserConfig::get($GLOBALS['user']->id)->SKIPLINKS_ENABLE;
        } else {
            //Use the global configuration:
            return Config::get()->SKIPLINKS_ENABLE;
        }
    }

    /**
     * Inserts container for skip links in page layout.
     */
    public static function insertContainer()
    {
        if (!self::isEnabled()) {
            return;
        }

        PageLayout::addBodyElements('<div id="skip_link_navigation" aria-busy="true"></div>');
    }

    /**
     * Adds a link to the list of skip links.
     *
     * @param string $name the displayed name of the links
     * @param string $url the url of the links
     * @param integer $position the position of the link in the list
     */
    public static function addLink(string $name, string $url, $position = null)
    {
        $position = (!$position || $position < 1) ? count(self::$links) + 100 : (int) $position;
        self::$links[$url] = [
            'name'     => $name,
            'url'      => $url,
            'position' => $position,
        ];
    }

    /**
     * Adds a link to an anker on the same page to the list of skip links.
     *
     * @param string  $name     the displayed name of the links
     * @param string  $id       the id of the anker
     * @param integer $position the position of the link in the list
     */
    public static function addIndex($name, $id, $position = null)
    {
        $url = '#' . $id;
        self::addLink($name, $url, $position);
    }

    /**
     * Returns the formatted list of skip links
     *
     * @return string the formatted list of skip links
     */
    public static function getHTML()
    {
        if (!self::isEnabled() || count(self::$links) === 0) {
            return '';
        }

        usort(self::$links, function ($a, $b) {
            return $a['position'] - $b['position'];
        });

        $navigation = new Navigation('');
        foreach (array_values(self::$links) as $index => $link) {
            $navigation->addSubNavigation(
                "/skiplinks/link-{$index}",
                new Navigation($link['name'], $link['url'])
            );
        }
        return $GLOBALS['template_factory']->render('skiplinks', compact('navigation'));
    }
}
