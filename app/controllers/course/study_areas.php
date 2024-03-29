<?php
/**
 * Course_StudyAreasController
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 *
 * @author      Marcus Lunzenauer <mlunzena@uos.de>
 * @author      David Siegfried <david.siegfried@uni-vechta.de>
 * @category    Stud.IP
 * @since       3.2
 */

require_once 'lib/webservices/api/studip_lecture_tree.php';

class Course_StudyAreasController extends AuthenticatedController
{
    // see Trails_Controller#before_filter
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        // Search for course object
        $this->course = Course::findCurrent();
        $this->locked = LockRules::Check($this->course->id, 'sem_tree');

        // check course object and perms
        if (isset($this->course) && !$GLOBALS['perm']->have_studip_perm('tutor', $this->course->id)) {
            $this->set_status(403);
            return false;
        }

        // Init Studyareas-Step for
        $lv_groups_enabled = CourseWizardStepRegistry::findOneBySQL("
                `classname` = 'StudyAreasLVGroupsCombinedWizardStep'
                    AND `enabled` = 1");
        if ($lv_groups_enabled) {
            $this->step = new StudyAreasLVGroupsCombinedWizardStep();
        } else {
            $this->step = new StudyAreasWizardStep();
        }
        $this->values = [];
        $this->values[get_class($this->step)]['studyareas'] = $this->get_area_ids($this->course->id);
        $this->values[get_class($this->step)]['ajax_url'] = $this->url_for('course/study_areas/ajax');
        $this->values[get_class($this->step)]['no_js_url'] = $this->url_for('course/study_areas/show');

        PageLayout::setTitle($this->course->getFullname() . ' - ' . _('Studienbereiche'));
    }


    public function show_action()
    {
        Navigation::activateItem('course/admin/study_areas');
        $this->url_params = [];
        if (Request::get('from')) {
            $this->url_params['from'] = Request::get('from');
        }
        if (Request::get('open_node')) {
            $this->url_params['open_node'] = Request::get('open_node');
        }

        if ($this->course) {
            if ($GLOBALS['perm']->have_studip_perm('admin', $this->course->id)) {
                $widget = new CourseManagementSelectWidget();
                Sidebar::Get()->addWidget($widget);
            }
        }
        if (Request::get('open_node')) {
            $this->values[get_class($this->step)]['open_node'] = Request::get('open_node');
        }
        $this->is_activated = $this->is_activated();
        $this->is_required  = $this->is_required();

        $this->values[get_class($this->step)]['locked'] = $this->locked;
        $this->tree                                     = $this->step->getStepTemplate($this->values, 0, 0);
    }

    public function ajax_action()
    {
        $parameter = Request::getArray('parameter');
        $method = Request::get('method');

        switch ($method) {
            case 'searchSemTree':
                $json = $this->step->searchSemTree($parameter[0]);
                break;
            case 'getSemTreeLevel':
                $json = $this->step->getSemTreeLevel($parameter[0]);
                break;
            default:
                $json = $this->step->getAncestorTree($parameter[0]);
                break;
        }

        $this->render_json($json);
    }

    public function save_action()
    {
        if($this->locked) {
            throw new Trails_Exception(403);
        }

        $params = [];
        if (Request::get('open_node')) {
            $params['open_node'] = Request::get('open_node');
        }
        if (Request::get('from')) {
            $url = $this->url_for(Request::get('from'));
        } else {
            $url = $this->url_for('course/study_areas/show/' . $this->course->id);
        }

        if (Request::submittedSome('assign', 'unassign')) {
            if (Request::submitted('assign')) {
                $msg = $this->assign();
            }

            if (Request::submitted('unassign')) {
                $msg = $this->unassign();
            }

        } else {
            $studyareas = Request::getArray('studyareas');

            if (empty($studyareas) && $this->is_required()) {
                PageLayout::postError(_('Sie müssen mindestens einen Studienbereich auswählen'));
                $this->redirect($url);
                return;
            }
            if (!empty($studyareas) && !$this->is_activated()) {
                PageLayout::postError(_('Sie dürfen keine Studienbereiche zuweisen.'));
                $this->redirect($url);
                return;
            }

            try {
                $this->course->setStudyAreas($studyareas);
            } catch (UnexpectedValueException $e) {
                PageLayout::postError($e->getMessage());
            }
        }

        if (!$msg) {
            PageLayout::postSuccess(_('Die Studienbereichszuordnung wurde übernommen.'));
        } else {
            PageLayout::postError($msg);
        }
        if (Request::isDialog()) {
            $this->response->add_header('X-Dialog-Close', 1);
            $this->response->add_header('X-Dialog-Execute', 'STUDIP.AdminCourses.App.loadCourse');
            $this->render_text($this->course->id);
        } else {
            $this->redirect($url);
        }
    }

    public function unassign()
    {
        $msg = null;
        $assigned = $this->course->study_areas->pluck('sem_tree_id');
        foreach (array_keys(Request::getArray('unassign')) as $remove) {
            if (false !== ($pos = array_search($remove, $assigned))) {
                unset($assigned[$pos]);
            }
        }

        if (empty($assigned) && $this->is_required()) {
            return _('Sie müssen mindestens einen Studienbereich auswählen');
        }

        $this->course->setStudyAreas($assigned);

        return $msg;
    }

    public function assign()
    {
        $msg = null;
        $assigned = array_keys(Request::getArray('assign'));

        if ($this->course->study_areas) {
            $assigned = array_unique(array_merge($assigned, $this->course->study_areas->pluck('sem_tree_id')));
        }

        $this->course->setStudyAreas($assigned);

        return $msg;
    }


    public function get_area_ids($course_id)
    {
        $selection = StudipStudyArea::getStudyAreasForCourse($course_id);

        return array_keys($selection->toGroupedArray('sem_tree_id'));
    }

    /**
     * Check whether the assignmenet of study areas is required.
     *
     * @return boolean True if required.
     */
    private function is_required()
    {
        $sem_class = $this->course->getSemClass();
        if (get_class($this->step) === 'StudyAreasLVGroupsCombinedWizardStep') {
            if ($sem_class['module']) {
                $lv_gruppen = Lvgruppe::findBySeminar($this->course->id);
                if (count($lv_gruppen)) {
                    return false;
                }
            }
        }

        return (bool) $sem_class['bereiche'];
    }

    private function is_activated()
    {
        $sem_class = $this->course->getSemClass();
        return (bool) $sem_class['bereiche'];
    }
}
