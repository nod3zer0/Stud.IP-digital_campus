<?php

/**
 * @property BlubberThread[] $elements
 */
class BlubberThreadsWidget extends SidebarWidget
{
    protected $active_thread = null;
    protected $with_composer = false;

    /**
     * @param BlubberThread $thread
     */
    public function addThread($thread)
    {
        $this->elements[] = $thread;
    }

    public function setActive($thread_id)
    {
        $this->active_thread = $thread_id;
    }

    public function withComposer($with = true)
    {
        $this->with_composer = $with;
    }

    public function render($variables = [])
    {
        $template = $GLOBALS['template_factory']->open('blubber/threads-overview.php');
        if (count($this->elements) > 30) {
            array_pop($this->elements);
            $template->more_down = true;
        }

        $json = [];
        foreach ($this->elements as $thread) {
            $unseen_comments = BlubberComment::countBySQL("thread_id = ? AND mkdate >= ?", [
                $thread->getId(),
                $thread->getLastVisit()
            ]);

            $json[] = [
                'thread_id'       => $thread->getId(),
                'avatar'          => $thread->getAvatar(),
                'name'            => $thread->getName(),
                'timestamp'       => (int) $thread->getLatestActivity(),
                'mkdate'          => (int) $thread->mkdate,
                'unseen_comments' => $unseen_comments,
                'notifications'   => $thread->id === 'global' || ($thread->context_type === 'course' && !$GLOBALS['perm']->have_perm('admin')),
                'followed'        => $thread->isFollowedByUser(),
            ];
        }

        $template->threads = $this->elements;
        $template->with_composer = $this->with_composer;
        $template->json = $json;
        return $template->render();
    }
}
