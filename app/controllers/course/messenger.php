<?php
class Course_MessengerController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        PageLayout::setBodyElementId('blubber-index');
        PageLayout::setHelpKeyword('Basis/InteraktionBlubber');
        PageLayout::setTitle(_('Blubber'));
    }

    public function course_action($thread_id = null)
    {
        if (Context::get()) {
            PageLayout::setTitle(Context::get()->getFullname() . ' - ' . _('Blubber'));
        }

        if (Navigation::hasItem('/course/blubber')) {
            Navigation::activateItem('/course/blubber');
        }

        $this->search = '';
        $this->threads = BlubberThread::findByContext(Context::get()->id, true, Context::getType());
        $this->thread = null;
        $this->threads_more_down = 0;

        if (!$thread_id) {
            $thread_id = $GLOBALS['user']->cfg->BLUBBER_DEFAULT_THREAD;
        }
        if ($thread_id) {
            foreach ($this->threads as $thread) {
                if ($thread->getId() === $thread_id) {
                    $this->thread = $thread;
                    break;
                }
            }
        }
        if (!$this->thread || Request::get('thread') === 'new') {
            $threads = array_reverse($this->threads);
            $this->thread = array_pop($threads);
        }

        if ($this->thread) {
            $this->thread->markAsRead();
        }

        if (!Avatar::getAvatar($GLOBALS['user']->id)->is_customized()) {
            $_SESSION['already_asked_for_avatar'] = true;
            PageLayout::postInfo(
                sprintf(
                    _('Wollen Sie ein Avatar-Bild nutzen? %sLaden Sie jetzt ein Bild hoch%s.'),
                    '<a href="' .
                        URLHelper::getURL('dispatch.php/avatar/update/user/' . $GLOBALS['user']->id) .
                        '" data-dialog>',
                    '</a>'
                )
            );
        }
        $this->buildSidebar();

        if (Request::isDialog()) {
            PageLayout::setTitle($this->thread->getName());
            $this->render_template('blubber/dialog');
        } else {
            $this->render_template('blubber/index', $this->layout);
        }
    }

    protected function buildSidebar()
    {
        $sidebar = Sidebar::Get();
        $sidebar->addWidget(new VueWidget('blubber-search-widget'));
        $sidebar->addWidget(new VueWidget('blubber-threads-widget'));
    }
}
