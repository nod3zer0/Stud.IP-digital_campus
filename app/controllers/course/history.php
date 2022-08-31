<?php
final class Course_HistoryController extends AuthenticatedController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!Context::isCourse()) {
            throw new Exception('History view is only available for courses');
        }
        if (!$GLOBALS['perm']->have_studip_perm('admin', Context::getId())) {
            throw new AccessDeniedException();
        }

        Navigation::activateItem('/course/main/history');
        PageLayout::setTitle(_('Änderungsverlauf'));
    }

    public function index_action()
    {
        $this->history = $this->getHistory(Context::get());
    }

    private function getHistory(Course $course): array
    {
        $result = [];
        LogEvent::findEachBySQL(
            function (LogEvent $event) use (&$result) {
                if (!isset($result[$event->action_id])) {
                    $result[$event->action_id] = [
                        'name'   => "{$event->action->name}: {$event->action->description}",
                        'events' => [],
                    ];
                }

                $result[$event->action_id]['events'][] = $event;
            },
            "? IN (affected_range_id, coaffected_range_id, user_id) ORDER BY mkdate DESC",
            [$course->id]
        );

        $result = array_values($result);
        usort($result, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return $result;
    }
}
