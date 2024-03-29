<?php
/**
 * semester.php - controller class for the semester administration
 *
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @author    Hermann Schröder <hermann.schroeder@uni-oldenburg.de>
 * @author    Michael Riehemann <michael.riehemann@uni-oldenburg.de>
 * @license   GPL2 or any later version
 * @category  Stud.IP
 * @package   admin
 * @since     2.1
 */

class Admin_SemesterController extends AuthenticatedController
{
    /**
     * common tasks for all actions
     *
     * @param String $action Action that has been called
     * @param Array  $args   List of arguments
     */
    public function before_filter (&$action, &$args)
    {
        parent::before_filter($action, $args);

        // user must have root permission
        $GLOBALS['perm']->check('root');

        //setting title and navigation
        PageLayout::setTitle(_('Verwaltung von Semestern'));
        Navigation::activateItem('/admin/locations/semester');

        // Extract and bind filter option
        $this->filter = Request::option('filter');
        if ($this->filter) {
            URLHelper::addLinkParam('filter', $this->filter);
        }

        // Setup sidebar
        $this->setSidebar();
    }

    /**
     * Display all informations about the semesters
     */
    public function index_action()
    {
        $this->semesters = array_reverse(Semester::getAll());

        // Filter data?
        if ($this->filter === 'current') {
            $this->semesters = array_filter($this->semesters, function ($semester) {
                return !$semester->past;
            });
        } elseif ($this->filter === 'past') {
            $this->semesters = array_filter($this->semesters, function ($semester) {
                return $semester->past;
            });
        }
    }

    /**
     * This method edits an existing semester or creates a new semester.
     *
     * @param mixed $id Id of the semester or null to create a semester.
     */
    public function edit_action($id = null)
    {
        $this->semester = new Semester($id);

        PageLayout::setTitle($this->semester->isNew() ? _('Semester anlegen') : _('Semester bearbeiten'));

        if (Request::isPost()) {
            CSRFProtection::verifyUnsafeRequest();

            // Extract values
            $this->semester->name           = Request::i18n('name');
            $this->semester->semester_token = Request::i18n('token');
            $this->semester->beginn         = $this->getTimeStamp('beginn');
            $this->semester->ende           = $this->getTimeStamp('ende', '23:59:59');
            $this->semester->vorles_beginn  = $this->getTimeStamp('vorles_beginn');
            $this->semester->vorles_ende    = $this->getTimeStamp('vorles_ende', '23:59:59');
            $this->semester->sem_wechsel = $this->getTimeStamp('semesterwechsel')?:null;

            $this->semester->external_id    = Request::get('external_id');

            // Validate
            $errors = $this->validateSemester($this->semester);

            // If valid, try to store the semester
            if (!$errors && $this->semester->store() === false) {
                $errors[] = _('Fehler bei der Speicherung Ihrer Daten. Bitte überprüfen Sie Ihre Angaben.');
            }

            // Output potential errors or show success message and relocate
            if (count($errors) === 1) {
                $error = reset($errors);
                PageLayout::postError($error);
            } elseif (!empty($errors)) {
                $message = _('Ihre eingegebenen Daten sind ungültig.');
                PageLayout::postError($message, $errors);
            } else {
                $message = _('Das Semester wurde erfolgreich gespeichert.');
                PageLayout::postSuccess($message);

                $this->relocate('admin/semester');
            }

            $this->errors = $errors;
        }
    }

    /**
     * This method deletes a semester or a bundle of semesters.
     *
     * @param string $id Id of the semester (or 'bulk' for a bulk operation)
     */
    public function delete_action($id)
    {
        $ids = $id === 'bulk'
             ? Request::optionArray('ids')
             : [$id];

        if (count($ids)) {
            $errors  = [];
            $deleted = 0;

            $semesters = Semester::findMany($ids);
            foreach ($semesters as $semester) {
                if ($semester->absolute_seminars_count > 0) {
                    $errors[] = sprintf(_('Das Semester "%s" hat noch Veranstaltungen und kann daher nicht gelöscht werden.'), $semester->name);
                } elseif (!$semester->delete()) {
                    $errors[] = sprintf(_('Fehler beim Löschen des Semesters "%s".'), $semester->name);
                } else {
                    $deleted += 1;
                }
            }

            if (count($errors) === 1) {
                PageLayout::postError($errors[0]);
            } elseif (!empty($errors)) {
                $message = _('Beim Löschen der Semester sind folgende Fehler aufgetreten.');
                PageLayout::postError($message, $errors);
            }
            if ($deleted > 0) {
                $message = sprintf(_('%u Semester wurde(n) erfolgreich gelöscht.'), $deleted);
                PageLayout::postSuccess($message);
            }
        }

        $this->redirect('admin/semester');
    }


    /**
     * Validates the semester for required valies, properness of values
     * and possible overlaps with other semesters.
     *
     * The validation is also divided into these three steps, so the next
     * validation step only occurs when the previous one succeeded.
     *
     * @param Semester $semester Semester (data) to validate
     * @return Array filled with errors
     */
    protected function validateSemester(Semester $semester)
    {
        // Validation, step 1: Check required values
        $errors = [];
        if (!$this->semester->name) {
            $errors['name'] = _('Sie müssen den Namen des Semesters angeben.');
        }
        if (!$this->semester->beginn) {
            $errors['beginn'] = _('Sie müssen den Beginn des Semesters angeben.');
        }
        if (!$this->semester->ende) {
            $errors['ende'] = _('Sie müssen das Ende des Semesters angeben.');
        }
        if (!$this->semester->vorles_beginn) {
            $errors['vorles_beginn'] = _('Sie müssen den Beginn der Vorlesungzeit angeben.');
        }
        if (!$this->semester->vorles_ende) {
            $errors['vorles_ende'] = _('Sie müssen das Ende der Vorlesungzeit angeben.');
        }

        // Validation, step 2: Check properness of values
        if (empty($errors)) {
            if ($this->semester->beginn > $this->semester->vorles_beginn) {
                $errors['beginn'] = _('Der Beginn des Semester muss vor dem Beginn der Vorlesungszeit liegen.');
            }
            if ($this->semester->vorles_beginn > $this->semester->vorles_ende) {
                $errors['vorles_beginn'] = _('Der Beginn der Vorlesungszeit muss vor ihrem Ende liegen.');
            }
            if ($this->semester->vorles_ende > $this->semester->ende) {
                $errors['vorles_ende'] = _('Das Ende der Vorlesungszeit muss vor dem Semesterende liegen.');
            }
        }

        // Validation, step 3: Check overlapping with other semesters
        if (empty($errors)) {
            $collisions_beginn = Semester::findByTimestamp($this->semester->beginn);
            if ($collisions_beginn && $collisions_beginn->id !== $this->semester->id) {
                $errors[] = sprintf(_('Der Beginn des Semester überschneidet sich mit einem anderen Semester (%s)'), $collisions_beginn->name);
            }

            $collisions_ende = Semester::findByTimestamp($this->semester->ende);
            if ($collisions_ende && $collisions_ende->id !== $this->semester->id) {
                $errors[] = sprintf(_('Das Ende des Semester überschneidet sich mit einem anderen Semester (%s)'), $collisions_ende->name);
            }
        }

        return $errors;
    }

    /**
     * Checks a string if it is a valid date and returns the according
     * unix timestamp if valid.
     *
     * @param string $name  Parameter name to extract from request
     * @param string $time Optional time segment
     * @return mixed Unix timestamp or false if not valid
     */
    protected function getTimeStamp($name, $time = '0:00:00')
    {
        $date = Request::get($name);
        if ($date) {
            list($day, $month, $year) = explode('.', $date);
            if (checkdate($month, $day, $year)) {
                return strtotime($date . ' ' . $time);
            }
        }
        return false;
    }

    /**
     * Adds the content to sidebar
     */
    protected function setSidebar()
    {
        $sidebar = Sidebar::Get();

        $views = $sidebar->addWidget(new ViewsWidget());
        $views->addLink(
            _('Alle Einträge'),
            $this->url_for('admin/semester', ['filter' => null])
        )->setActive(!$this->filter);
        $views->addLink(
            _('Aktuelle/zukünftige Einträge'),
            $this->url_for('admin/semester', ['filter' => 'current'])
        )->setActive($this->filter === 'current');
        $views->addLink(
            _('Vergangene Einträge'),
            $this->url_for('admin/semester', ['filter' => 'past'])
        )->setActive($this->filter === 'past');

        $links = $sidebar->addWidget(new ActionsWidget());
        $links->addLink(
            _('Neues Semester anlegen'),
            $this->url_for('admin/semester/edit', ['filter' => null]),
            Icon::create('add')
        )->asDialog('size=auto');
    }

    /**
     * This method locks a semester or a bundle of semesters.
     *
     * @param string $id Id of the semester (or 'bulk' for a bulk operation)
     */
    public function lock_action($id)
    {
        PageLayout::setTitle(_('Sperren von Semestern'));

        $this->id = $id;

        $ids = $id === 'bulk'
             ? Request::optionArray('ids')
             : [$id];

        if (count($ids) === 0) {
            throw new InvalidArgumentException(_('Es wurde kein Semester zum Sperren übergeben'));
        }

        $semesters = Semester::findMany($ids);

        if (Request::isPost()) {
            $errors = [];
            $locked = 0;

            $lock_enroll   = (bool) Request::int('lock_enroll');
            $degrade_users = (bool) Request::int('degrade_users');
            $lock_rule     = Request::get('lock_sem_all') ?: null;

            foreach ($semesters as $semester) {
                $semester->visible = false;

                if ($semester->store()) {
                    $this->lockCourses($semester, $lock_rule, $degrade_users, $lock_enroll);

                    $locked += 1;
                } else {
                    $errors[] = $semester->name;
                }
            }

            if (count($errors) === 1) {
                PageLayout::postError(sprintf(
                    _('Fehler beim Sperren des Semesters "%s".'),
                    htmlReady($errors[0])
                ));
            } elseif (count($errors) > 0) {
                $message = _('Beim Sperren der folgenden Semester sind Fehler aufgetreten:');
                PageLayout::postError($message, array_map('htmlReady', $errors));
            }
            if ($locked > 0) {
                $message = sprintf(_('%u Semester wurde(n) erfolgreich gesperrt.'), $locked);
                PageLayout::postSuccess($message);
            }

            $this->relocate('admin/semester');
            return;
        }

        $sum_courses = 0;
        $sem_names = [];
        foreach ($semesters as $semester) {
            $sum_courses += Course::countBySQL("INNER JOIN semester_courses ON (seminare.Seminar_id = semester_courses.course_id) WHERE semester_courses.semester_id = ?", [$semester->semester_id]);
            $sem_names[] = $semester->name;
        }

        $lock_info = [
            sprintf(ngettext(
                'Es wird folgendes Semester gesperrt: %s.',
                'Es werden folgende Semester gesperrt: %s.',
                $sum_courses
            ), implode(', ', $sem_names)),
            sprintf(_('Es werden %u Veranstaltungen geändert.'), $sum_courses),
            _('Unbegrenzt laufende Veranstaltungen werden nicht geändert.')
        ];

        PageLayout::postWarning(
            sprintf(_('Wollen sie wirklich %u Semester sperren?'), count($semesters)),
            array_map('htmlReady', $lock_info)
        );

        $this->all_lock_rules = LockRule::findAllByType('sem');
    }

    /**
     * This method unlocks a semester or a bundle of semesters.
     *
     * @param string $id Id of the semester (or 'bulk' for a bulk operation)
     */
    public function unlock_action($id)
    {
        $ids = $id === 'bulk'
             ? Request::optionArray('ids')
             : [$id];

        if (count($ids) > 0) {
            $errors   = [];
            $unlocked = 0;

            $semesters = Semester::findMany($ids);
            foreach ($semesters as $semester) {
                $semester->visible = true;

                if ($semester->store()) {
                    $unlocked += 1;
                } else {
                    $errors[] = sprintf(_('Fehler beim Entsperren des Semesters "%s".'), $semester->name);
                }
            }

            if (count($errors) === 1) {
                PageLayout::postError(htmlReady($errors[0]));
            } elseif (count($errors) > 0) {
                $message = _('Beim Entsperren der Semester sind folgende Fehler aufgetreten.');
                PageLayout::postError($message, array_map('htmlReady', $errors));
            }
            if ($unlocked > 0) {
                $message = sprintf(_('%u Semester wurde(n) erfolgreich entsperrt.'), $unlocked);
                PageLayout::postSuccess($message);
            }
        }

        $this->redirect('admin/semester');
    }

    /**
     * Locks all courses for a given semester.
     *
     * @param  Semester $semester      Semester to lock
     * @param  string   $lock_rule     Lock rule to apply (might be null for none)
     * @param  bool     $lock_enroll   Lock enrolment?
     * @param  bool     $degrade_users Degrade users?
     */
    private function lockCourses(Semester $semester, $lock_rule, $degrade_users, $lock_enroll)
    {
        // Querying for the locked courseset id is costly so we should cache
        // this
        static $locked_courseset_id = null;

        // Get course ids
        $query = "SELECT `course_id`
                  FROM `semester_courses`
                  JOIN `semester_data` USING (`semester_id`)
                  GROUP BY `course_id`
                  HAVING MAX(`beginn`) = ?";
        $course_ids = DBManager::get()->fetchFirst($query, [$semester->beginn]);

        // Leave early if no courses are affected
        if (count($course_ids) === 0) {
            return;
        }

        // Hide courses and set lock rule
        Course::findEachMany(
            function (Course $course) use ($lock_rule) {
                $course->visible = 0;
                $course->lock_rule = $lock_rule;
                $course->store();
            },
            [$course_ids]
        );

        // Degrade users
        if ($degrade_users) {
            CourseMember::findEachBySQL(
                function (CourseMember $cm) {
                    $cm->status = 'user';
                    $cm->store();
                },
                "`Seminar_id` IN (?) and `status` = 'autor'",
                [$course_ids]
            );
        }

        // Lock enrolment
        if ($lock_enroll) {
            if ($locked_courseset_id === null) {
                $locked_courseset_id = CourseSet::getGlobalLockedAdmissionSetId();
            }

            foreach ($course_ids as $id) {
                $cset = CourseSet::getSetForCourse($id);
                if ($cset) {
                    CourseSet::removeCourseFromSet($cset->getId(), $id);
                }
                CourseSet::addCourseToSet($locked_courseset_id, $id);
            }
        }

    }
}
