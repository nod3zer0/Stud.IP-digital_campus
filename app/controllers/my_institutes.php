<?php
require_once 'lib/meine_seminare_func.inc.php';

class MyInstitutesController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        if (!$GLOBALS['perm']->have_perm("root")) {
            Navigation::activateItem('/browse/my_institutes');
        }
        $this->user_id = $GLOBALS['user']->id;
        PageLayout::setHelpKeyword('Basis.MeineEinrichtungen');
        PageLayout::setTitle(_('Meine Einrichtungen'));
    }

    public function index_action()
    {
        $this->institutes = MyRealmModel::getMyInstitutes();

        if ($this->check_for_new($this->institutes)) {
            $this->reset = true;
        }

        $this->nav_elements = MyRealmModel::calc_single_navigation($this->institutes);

        $this->setupSidebar(
            $this->institutes,
            $this->check_for_new($this->institutes)
        );
    }

    public function decline_inst_action($inst_id)
    {
        $institut     = Institute::find($inst_id);
        $ticket_check = Seminar_Session::check_ticket(Request::option('studipticket'));

        if (Request::option('cmd') !== 'kill' && Request::get('cmd') !== 'back') {
            $this->flash['decline_inst'] = true;
            $this->flash['inst_id']      = $inst_id;
            $this->flash['name']         = $institut->name;
            $this->flash['studipticket'] = Seminar_Session::get_ticket();
        } elseif (Request::get('cmd') === 'kill' && $ticket_check && Request::get('cmd') !== 'back') {
            $changed = InstituteMember::deleteBySQL(
                "user_id = ? AND Institut_id = ? AND inst_perms = 'user'",
                [$this->user_id, $inst_id]
            );

            if ($changed > 0) {
                PageLayout::postSuccess(sprintf(
                    _('Die Zuordnung zur Einrichtung %s wurde aufgehoben.'),
                    '<strong>' . htmlReady($institut->name) . '</strong>'
                ));
            } else {
                PageLayout::postError(_('Datenbankfehler'));
            }
        }
        $this->redirect('my_institutes/index');
    }

    public function tabularasa_action($timestamp = null)
    {
        $institutes = MyRealmModel::getMyInstitutes();
        foreach ($institutes as $index => $institut) {
            MyRealmModel::setObjectVisits($institutes[$index], $institut['institut_id'], $this->user_id, $timestamp);
        }

        PageLayout::postSuccess(_('Alles als gelesen markiert!'));
        $this->redirect('my_institutes/index');
    }

    protected function check_for_new($my_obj): bool
    {
        if(!empty($my_obj)) {
            foreach ($my_obj as $inst) {
                if ($this->check_institute($inst)) {
                    return true;
                }
            }
        }
        return false;
    }

    protected function check_institute($institute): bool
    {
        if ($institute['visitdate'] || $institute['last_modified']) {
            if ($institute['visitdate'] <= $institute["chdate"] || $institute['last_modified'] > 0) {
                $last_modified = ($institute['visitdate'] <= $institute["chdate"]
                && $institute["chdate"] > $institute['last_modified'] ? $institute["chdate"] : $institute['last_modified']);
                if ($last_modified) {
                    return true;
                }
            }
        }

        $plugins = $institute['navigation'];

        foreach ($plugins as $navigation) {
            if ($navigation && $navigation->isVisible(true) && $navigation->hasBadgeNumber()) {
                return true;
            }
        }

        return false;
    }

    private function setupSidebar(array $institutes, bool $reset)
    {
        $links = Sidebar::Get()->addWidget(new ActionsWidget());
        if ($reset) {
            $links->addLink(
                _('Alles als gelesen markieren'),
                $this->tabularasaURL(time()),
                Icon::create('accept')
            );
        }
        if ($GLOBALS['perm']->have_perm('dozent') && count($institutes) > 0) {
            $links->addLink(
                _('Einrichtungsdaten bearbeiten'),
                URLHelper::getURL('dispatch.php/settings/statusgruppen'),
                Icon::create('edit')
            );
        }
        if ($GLOBALS['perm']->have_perm('autor')) {
            $links->addLink(
                _('Einrichtungen suchen'),
                URLHelper::getURL('dispatch.php/search/globalsearch#GlobalSearchInstitutes'),
                Icon::create('search')
            );
            $links->addLink(
                _('Studiendaten bearbeiten'),
                URLHelper::getURL('dispatch.php/settings/studies'),
                Icon::create('person')
            );
        }
    }
}
