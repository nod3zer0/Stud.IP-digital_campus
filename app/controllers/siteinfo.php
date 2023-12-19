<?php
# Lifter007: TEST

/**
 * siteinfo - display information about Stud.IP
 *
 * @author    Ansgar Bockstiegel
 * @copyright 2008 Ansgar Bockstiegel
 * @license   GPL2 or any later version
 */
class SiteinfoController extends StudipController
{
    protected $with_session = true;

    /**
     * @var Siteinfo
     */
    private $si;

    /**
     * common tasks for all actions
     */
    public function before_filter (&$action, &$args)
    {
        parent::before_filter($action, $args);

        // Siteinfo-Class is defined in models/siteinfo.php
        $this->si = new Siteinfo();

        $this->populate_ids($args);
        $detail            = $this->si->get_detail($this->currentdetail);
        $this->page_is_draft = $detail['draft_status'] ?? false;
        $this->page_disabled_nobody = $detail['page_disabled_nobody'] ?? false;

        if (is_object($GLOBALS['perm']) && $GLOBALS['perm']->have_perm('root')) {
            $this->setupSidebar();
        } else {
            $action = 'show';
            if ($this->page_is_draft || ($this->page_disabled_nobody && $GLOBALS['user']->id === 'nobody')) {
                throw new Trails_Exception(404);
            }
        }
        $this->add_navigation($action);

        PageLayout::setTitle(_('Impressum'));
        PageLayout::setTabNavigation('/footer/siteinfo');
    }

    //the first element of the unconsumed trails-path determines the rubric
    //the second element defines the page(detail)
    //if they are missing the first detail/rubric is the fallback
    protected function populate_ids($args)
    {
        if (isset($args[0]) && is_numeric($args[0])) {
            $this->currentrubric = $args[0];
            if (isset($args[1]) && is_numeric($args[1])) {
                $this->currentdetail = $args[1];
            } else {
                $this->currentdetail = $this->si->first_detail_id($args[0], !$GLOBALS['perm']->have_perm('root'), $GLOBALS['user']->id === 'nobody');
            }
        } else {
            $this->currentrubric = $this->si->first_rubric_id();
            $this->currentdetail = $this->si->first_detail_id(null, !$GLOBALS['perm']->have_perm('root'), $GLOBALS['user']->id === 'nobody');
        }
    }

    protected function add_navigation($action)
    {
        foreach ($this->si->get_all_rubrics() as $rubric) {
            $rubric[1] = language_filter($rubric[1]);
            if ($rubric[1] == '') {
                $rubric[1] = _('unbenannt');
            }
            Navigation::addItem('/footer/siteinfo/'.$rubric[0],
                new Navigation($rubric[1], $this->url_for('siteinfo/show/'.$rubric[0])));
        }

        foreach ($this->si->get_all_details() as $detail) {
            if ((!$GLOBALS['perm']->have_perm('root') && $detail['draft_status'])
                || ($detail['page_disabled_nobody'] && $GLOBALS['user']->id === 'nobody')) {
                continue;
            }
            $detail['name'] = language_filter($detail['name']);
            if ($detail['name'] == '') {
                $detail['name'] = _('unbenannt');
            }
            Navigation::addItem('/footer/siteinfo/'.$detail['rubric_id'].'/'.$detail['detail_id'],
                new Navigation($detail['name'], $this->url_for('siteinfo/show/'.$detail['rubric_id'].'/'.$detail['detail_id'])));
        }

        if ($action != 'new') {
            if ($this->currentdetail > 0) {
                Navigation::activateItem('/footer/siteinfo/'.$this->currentrubric.'/'.$this->currentdetail);
            } else {
                Navigation::activateItem('/footer/siteinfo/'.$this->currentrubric);
            }
        }
    }

    protected function setupSidebar()
    {
        $sidebar = Sidebar::get();

        if (count($this->si->get_all_rubrics())) {
            $actions = new ActionsWidget();
            $actions->setTitle(_('Seiten-Aktionen'));

            if ($this->currentrubric) {
                $actions->addLink(_('Neue Seite anlegen'),
                                  $this->url_for('siteinfo/new/' . $this->currentrubric), Icon::create('add'));
            }
            if ($this->currentdetail) {
                $actions->addLink(_('Seite bearbeiten'),
                                  $this->url_for('siteinfo/edit/' . $this->currentrubric . '/' . $this->currentdetail), Icon::create('edit'));
                $actions->addLink(_('Seite löschen'),
                                  $this->url_for('siteinfo/delete/' . $this->currentrubric . '/' . $this->currentdetail), Icon::create('trash'));
            }

            $sidebar->addWidget($actions);
        }


        $actions = new ActionsWidget();
        $actions->setTitle(_('Rubrik-Aktionen'));

        $actions->addLink(_('Neue Rubrik anlegen'),
                          $this->url_for('siteinfo/new'), Icon::create('add'));
        if ($this->currentrubric) {
            $actions->addLink(_('Rubrik bearbeiten'),
                              $this->url_for('siteinfo/edit/' . $this->currentrubric), Icon::create('edit'));
            $actions->addLink(_('Rubrik löschen'),
                              $this->url_for('siteinfo/delete/' . $this->currentrubric), Icon::create('trash'));
        }

        $sidebar->addWidget($actions);
    }

    /**
     * Display the siteinfo
     */
    public function show_action()
    {
        $draft_status = $this->si->get_detail_draft_status($this->currentdetail);
        if ($draft_status == 1 && !$GLOBALS['perm']->have_perm('root')) {
            throw new AccessDeniedException();
        }
        $this->output = $this->si->get_detail_content_processed($this->currentdetail);
        if ($this->page_is_draft) {
            PageLayout::postInfo(_('Diese Seite befindet sich im Entwurfsmodus und ist daher noch unsichtbar.'));
        }
    }

    public function new_action($givenrubric = null)
    {
        $GLOBALS['perm']->check('root');
        $this->edit_rubric = null;
        if ($givenrubric === null) {
            Navigation::addItem('/footer/siteinfo/rubric_new',
                                new AutoNavigation(_('Neue Rubrik'),
                                                   $this->url_for('siteinfo/new')));
            $this->edit_rubric = true;
        } else {
            Navigation::addItem('/footer/siteinfo/' . $this->currentrubric . '/detail_new',
                                new AutoNavigation(_('Neue Seite'),
                                                   $this->url_for('siteinfo/new/' . $this->currentrubric)));
            $this->rubrics = $this->si->get_all_rubrics();
        }
    }

    public function edit_action($givenrubric = null, $givendetail = null)
    {
        $GLOBALS['perm']->check('root');
        $this->edit_rubric = null;
        if (is_numeric($givendetail)) {
            $this->rubrics     = $this->si->get_all_rubrics();
            $detail            = $this->si->get_detail($this->currentdetail);
            $this->rubric_id   = $detail['rubric_id'];
            $this->detail_name = $detail['name'];
            $this->content     = $detail['content'];
            $this->draft_status = $detail['draft_status'];
            $this->page_disabled_nobody = $detail['page_disabled_nobody'];
            $this->page_position = $detail['position'];
        } else {
            $this->edit_rubric = true;
            $this->rubric_id = $this->currentrubric;
        }
        $rubric = $this->si->rubric($this->currentrubric);
        $this->rubric_name = $rubric['name'];
        $this->rubric_position = $rubric['position'];
    }

    public function save_action()
    {

        $GLOBALS['perm']->check('root');

        CSRFProtection::verifyUnsafeRequest();
        $detail_name = Request::get('detail_name');
        $rubric_name = Request::get('rubric_name');
        $content     = Request::get('content');
        $rubric_id   = Request::int('rubric_id');
        $detail_id   = Request::int('detail_id');
        $draft_status = Request::submitted('draft_status');
        $page_disabled_nobody = Request::submitted('page_disabled_nobody');
        $page_position = Request::int('page_position');
        $rubric_position = Request::int('rubric_position');

        if ($rubric_id) {
            if ($detail_id) {
                list($rubric, $detail) = $this->si->save('update_detail', compact('rubric_id', 'detail_name', 'content', 'detail_id', 'draft_status', 'page_disabled_nobody', 'page_position'));
            } else {
                if (isset($content)) {
                    if (!$page_position) {
                        $page_position = $this->si->get_detail_max_position($rubric_id) + 1;
                    }
                    list($rubric, $detail) = $this->si->save('insert_detail', compact('rubric_id', 'detail_name','content', 'draft_status', 'page_disabled_nobody', 'page_position'));
                } else {
                    list($rubric, $detail) = $this->si->save('update_rubric', compact('rubric_id', 'rubric_name', 'rubric_position'));
                }
            }
        } else {
            if (!$rubric_position) {
                $rubric_position = $this->si->get_rubric_max_position() + 1;
            }
            list($rubric, $detail) = $this->si->save('insert_rubric', compact('rubric_name', 'rubric_position'));
        }
        $this->redirect('siteinfo/show/' . $rubric . '/' . $detail);
    }

    public function delete_action($givenrubric = null, $givendetail = null, $execute = false)
    {
        $GLOBALS['perm']->check('root');

        if ($execute) {
            CSRFProtection::verifyUnsafeRequest();
            if ($givendetail === 'all') {
                $this->si->delete('rubric', $this->currentrubric);
                $this->redirect('siteinfo/show/');
            } else {
                $this->si->delete('detail', $this->currentdetail);
                $this->redirect('siteinfo/show/' . $this->currentrubric);
            }
        } else {
            if (is_numeric($givendetail)) {
                $this->detail = true;
            }
            $this->output = $this->si->get_detail_content_processed($this->currentdetail);
        }
    }
}
