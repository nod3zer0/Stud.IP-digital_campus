<?php

use Courseware\PublicLink;

class Courseware_PublicController extends StudipController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        PageLayout::setTitle(_('Courseware'));
        PageLayout::setHelpKeyword('Basis.Courseware');
    }

    public function index_action()
    {
        $this->invalid = true;
        $this->link_id = Request::option('link');
        if ($this->link_id) {
            $publicLink = PublicLink::find($this->link_id);
            $this->invalid = $publicLink === null;
            if (!$this->invalid) {
                $this->expired = $publicLink->isExpired();
                $this->link_pass = $publicLink->password;
                $this->entry_element_id = $publicLink->structural_element_id;
            }
        }
    }
}
