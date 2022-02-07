<?php

class Admin_CoursewareController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
        $GLOBALS['perm']->check('root');
        PageLayout::setTitle(_('Coursewareverwaltung'));
        Navigation::activateItem('/admin/locations/courseware');
    }

    public function index_action()
    {
        $this->setSidebar();
    }

    private function setSidebar()
    {
        $sidebar = Sidebar::Get();
        $views = new TemplateWidget(
            _('Ansichten'),
            $this->get_template_factory()->open('admin/courseware/admin_view_widget')
        );
        $sidebar->addWidget($views)->addLayoutCSSClass('courseware-admin-view-widget');

        $views = new TemplateWidget(
            _('Aktionen'),
            $this->get_template_factory()->open('admin/courseware/admin_action_widget')
        );
        $sidebar->addWidget($views)->addLayoutCSSClass('courseware-admin-action-widget');
    }

}