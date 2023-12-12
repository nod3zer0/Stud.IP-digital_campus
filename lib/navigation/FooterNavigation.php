<?php
/*
 * FooterNavigation.php - navigation for the footer on every page
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.1
 */

class FooterNavigation extends Navigation
{
    /**
     * Initialize a new Navigation instance.
     */
    public function __construct()
    {
        parent::__construct(_('Footer'));
    }

    public function initSubNavigation()
    {
        parent::initSubNavigation();

        // imprint
        $this->addSubNavigation('siteinfo', new Navigation(_('Impressum'), 'dispatch.php/siteinfo/show?cancel_login=1'));

        // sitemap
        if (is_object($GLOBALS['user']) && $GLOBALS['user']->id !== 'nobody') {
            $this->addSubNavigation('sitemap', new Navigation(_('Sitemap'), 'dispatch.php/sitemap/'));
        }

        //studip
        $this->addSubNavigation('studip', new Navigation(_('Stud.IP'), 'http://www.studip.de/'));

        // Datenschutzerklärung

        $privacy_url = Config::get()->PRIVACY_URL;
        if ($this->checkSiteinfoURL($privacy_url)) {
            $this->addSubNavigation(
                'privacy',
                new Navigation(
                    _('Datenschutz'),
                    URLHelper::getURL($privacy_url, ['cancel_login' => 1], true)
                )
            );
        }

        $a11yurl = Config::get()->ACCESSIBILITY_DISCLAIMER_URL;
        if ($this->checkSiteinfoURL($a11yurl)) {
            $this->addSubNavigation(
                'a11ydisclaimer',
                new Navigation(
                    _('Barrierefreiheitserklärung'),
                    URLHelper::getURL($a11yurl, ['cancel_login' => 1], true)
                )
            );
        }

        if (
            Config::get()->REPORT_BARRIER_MODE === 'on'
            || (
                Config::get()->REPORT_BARRIER_MODE === 'logged-in'
                && User::findCurrent()
            )
        ) {
            $this->addSubNavigation(
                'report_barrier',
                new Navigation(
                    _('Barriere melden'),
                    URLHelper::getURL(
                        'dispatch.php/accessibility/forms/report_barrier',
                        ['page' => Request::url(), 'cancel_login' => '1']
                    )
                )
            );
        }

        $easy_read_url = Config::get()->EASY_READ_URL;
        if ($this->checkSiteinfoURL($easy_read_url)) {
            $this->addSubNavigation(
                'easy_read',
                new Navigation(
                    _('Leichte Sprache'),
                    URLHelper::getURL($easy_read_url, ['cancel_login' => 1], true)
                )
            );
        }
    }

    private function checkSiteinfoURL($url)
    {
        if (str_starts_with($url, 'dispatch.php/siteinfo')) {
            $url_parts = explode('/', $url);
            $detail_id = $url_parts[4];
            $si = new Siteinfo();
            try {
                $isdraft = $si->get_detail_draft_status($detail_id);
                if ($isdraft) {
                    return '';
                }
            } catch (PDOException $e) {}

        }
        return $url;
    }
}
