<?php
/**
 * Admin_Cronjobs_Tasks_Controller
 *
 * Controller class for cronjob tasks
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     GPL2 or any later version
 * @category    Stud.IP
 * @since       2.4
 */
class Admin_Cronjobs_TasksController extends AuthenticatedController
{
    protected $_autobind = true;

    /**
     * Set up this controller.
     *
     * @param String $action Name of the action to be invoked
     * @param Array  $args   Arguments to be passed to the action method
     */
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        Navigation::activateItem('/admin/config/cronjobs');
        PageLayout::setTitle(_('Cronjob-Verwaltung') . ' - ' . _('Aufgaben'));

        // Setup sidebar
        $sidebar = Sidebar::Get();

        // Aktionen
        $actions = $sidebar->addWidget(new ViewsWidget());
        $actions->addLink(
            _('Cronjobs verwalten'),
            $this->url_for('admin/cronjobs/schedules')
        );
        $actions->addLink(
            _('Aufgaben verwalten'),
            $this->url_for('admin/cronjobs/tasks')
        )->setActive(true);
        $actions->addLink(
            _('Logs anzeigen'),
            $this->url_for('admin/cronjobs/logs')
        );
    }

    /**
     * Displays all available tasks.
     */
    public function index_action()
    {
        $this->tasks = CronjobTask::findBySQL('1');
    }

    /**
     * Activates a tasks.
     *
     * @param CronjobTask $task Task to activate
     */
    public function activate_action(CronjobTask $task)
    {
        $task->active = true;
        $task->store();

        if (!Request::isXhr()) {
            // Report how many actual cronjobs were activated
            $activated = count($task->schedules->filter(function ($schedule) {
                return $schedule->active;
            }));

            $message = sprintf(_('Die Aufgabe und %u Cronjob(s) wurden aktiviert.'), $activated);
            PageLayout::postSuccess($message);
        }
        $this->redirect("admin/cronjobs/tasks/index#task-{$task->id}");
    }

    /**
     * Deactivates a tasks.
     *
     * @param CronjobTask $task Task to deactivate
     */
    public function deactivate_action(CronjobTask $task)
    {
        $task->active = false;
        $task->store();

        if (!Request::isXhr()) {
            // Report how many actual cronjobs were activated
            $deactivated = count($task->schedules->filter(function ($schedule) {
                return $schedule->active;
            }));

            $message = sprintf(_('Die Aufgabe und %u Cronjob(s) wurden deaktiviert.'), $deactivated);
            PageLayout::postSuccess($message);
        }
        $this->redirect("admin/cronjobs/tasks/index#task-{$task->id}");
    }

    /**
     * Deletes a tasks.
     *
     * @param CronjobTask $task Task to delete
     */
    public function delete_action(CronjobTask $task)
    {
        CSRFProtection::verifyUnsafeRequest();
        $deleted = $task->schedules->count();
        $task->delete();

        $message = sprintf(_('Die Aufgabe und %u Cronjob(s) wurden gelöscht.'), $deleted);
        PageLayout::postSuccess($message);

        $this->redirect('admin/cronjobs/tasks/index');
    }

    /**
     * Performs a bulk operation on a set of tasks. Operation can be either
     * activating, deactivating or deleting.
     */
    public function bulk_action()
    {
        $action = Request::option('action');
        $ids    = Request::optionArray('ids');
        $tasks  = CronjobTask::findMany($ids);

        if ($action === 'activate') {
            $tasks = array_filter($tasks, function ($item) {
                return !$item->active;
            });
            foreach ($tasks as $task) {
                $task->active = true;
                $task->store();
            }

            $n = count($tasks);
            $message = sprintf(ngettext('%u Aufgabe wurde aktiviert.', '%u Aufgaben wurden aktiviert.', $n), $n);
            PageLayout::postSuccess($message);
        } elseif ($action === 'deactivate') {
            $tasks = array_filter($tasks, function ($item) {
                return $item->active;
            });
            foreach ($tasks as $task) {
                $task->active = false;
                $task->store();
            }

            $n = count($tasks);
            $message = sprintf(ngettext('%u Aufgabe wurde deaktiviert.', '%u Aufgaben wurden deaktiviert.', $n), $n);
            PageLayout::postSuccess($message);
        } elseif ($action === 'delete') {
            foreach ($tasks as $task) {
                $task->delete();
            }

            $n = count($tasks);
            $message = sprintf(ngettext('%u Aufgabe wurde gelöscht.', '%u Aufgaben wurden gelöscht.', $n), $n);
            PageLayout::postSuccess($message);
        }

        $this->redirect('admin/cronjobs/tasks/index');
    }

    /**
     * Executes a single task
     *
     * @param CronjobTask $task Task to execute
     */
    public function execute_action(CronjobTask $task)
    {
        PageLayout::setTitle(_('Cronjob-Aufgabe ausführen'));

        if (Request::isPost()) {
            $parameters = Request::getArray('parameters');
            $parameters = isset($parameters[$this->task->id])
                        ? $parameters[$this->task->id]
                        : [];

            ob_start();
            $this->task->engage(null, $parameters);
            $this->result = ob_get_clean();
        } else {
            $this->schedule = new CronjobSchedule();
        }
    }
}
