<?php

abstract class ForumController extends StudipController {
    protected $with_session = true;

    /* * * * * * * * * * * * * * * * * * * * * * * * * */
    /* * * * * H E L P E R   F U N C T I O N S * * * * */
    /* * * * * * * * * * * * * * * * * * * * * * * * * */
    function getId()
    {
        return ForumHelpers::getSeminarId();
    }

    /**
     * Common code for all actions: set default layout and page title.
     *
     * @param type $action
     * @param type $args
     */
    function before_filter(&$action, &$args)
    {
        $this->validate_args($args, ['option', 'option']);

        parent::before_filter($action, $args);

        $this->flash = Trails_Flash::instance();

        // Set help keyword for Stud.IP's user-documentation and page title
        PageLayout::setHelpKeyword('Basis.Forum');
        PageLayout::setTitle(Context::getHeaderLine() .' - '. _('Forum'));

        // the default for displaying timestamps
        $this->time_format_string = "%a %d. %B %Y, %H:%M";
        $this->time_format_string_short = "%d.%m.%Y, %H:%M";

        //$this->getId() depends on Context::get()
        checkObject();
        ForumVisit::setVisit($this->getId());
        if (Request::int('page')) {
            ForumHelpers::setPage(Request::int('page'));
        }

        $this->seminar_id = $this->getId();

        $this->no_entries = false;
        $this->highlight = [];
        $this->highlight_topic = '';
        $this->edit_posting = '';
        $this->js = '';
    }
}
