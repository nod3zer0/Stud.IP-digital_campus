<?php
/**
 * @author      Peter Thienel <thienel@data-quest.de>
 * @author
 * @license     GPL2 or any later version
 * @since       4.6
 */

class Studiengaenge_InformationenController extends MVVController
{
    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);

        if (!($GLOBALS['perm']->have_perm('root')
            || User::findCurrent()->hasRole('MVVAdmin'))) {
            throw new AccessDeniedException();
        }
        PageLayout::setTitle(_('Verwaltung der Studiengänge'));
        if (Navigation::hasItem('mvv/studiengaenge/informationen')) {
            Navigation::activateItem('mvv/studiengaenge/informationen');
        }
    }

    public function index_action()
    {
        $this->createSidebar();
        if ($GLOBALS['perm']->have_perm('root', $GLOBALS['user']->id)) {
            $this->studycourses = Fach::findBySQL('fach_id IN (SELECT DISTINCT(fach_id) FROM user_studiengang) ORDER BY name');
        } else {
            $inst_ids = SimpleCollection::createFromArray(Institute::findBySQL('Institut_id IN (SELECT institut_id FROM roles_user WHERE userid = :user_id)
                OR fakultaets_id IN (SELECT institut_id FROM roles_user WHERE userid = :user_id)',
                    [':user_id' => $GLOBALS['user']->user_id]))->pluck('institut_id');

            $this->studycourses = Fach::findBySQL('JOIN mvv_fach_inst as fach_inst ON (fach.fach_id = fach_inst.fach_id)
                WHERE fach_inst.institut_id IN (:inst_ids)
                GROUP BY fach.fach_id ORDER BY fach.name',
            [':inst_ids' => $inst_ids]);
        }
    }

    public function degree_action ()
    {
        $this->createSidebar('degrees');
        $this->degree = Degree::findBySQL('abschluss_id IN (SELECT DISTINCT(abschluss_id) FROM user_studiengang) ORDER BY name');
    }

    public function showdegree_action($studycourse_id, $nr = 0)
    {
        $this->studycourse = Fach::find($studycourse_id);
        $this->nr = $nr;
    }

    public function showstudycourse_action($degree_id, $nr = 0)
    {
        $this->nr = $nr;
        $this->degree = Abschluss::find($degree_id);
        $this->professions = $this->degree->professions;

        if ($GLOBALS['perm']->have_perm('root',$GLOBALS['user']->id)) {
            $this->studycourses = $this->degree->professions;
        } else {
            $inst_ids = SimpleCollection::createFromArray(Institute::findBySQL('Institut_id IN (SELECT institut_id FROM roles_user WHERE userid = :user_id)
                OR fakultaets_id IN (SELECT institut_id FROM roles_user WHERE userid = :user_id)',
                    [':user_id' => $GLOBALS['user']->user_id]))->pluck('institut_id');

            $this->studycourses = Fach::findBySQL('JOIN mvv_fach_inst as fach_inst ON (fach.fach_id = fach_inst.fach_id)
                WHERE fach_inst.institut_id IN (:inst_ids)
                GROUP BY fach.fach_id ORDER BY fach.name',
            [':inst_ids' => $inst_ids]);
        }
    }

    public function messagehelper_action()
    {
        $fach = Fach::find(Request::get('fach_id'));
        $degree = Degree::find(Request::get('abschluss_id'));

        if (!$degree && $fach) {
            $users = UserStudyCourse::findBySql('fach_id = :fach_id', [':fach_id' => $fach->id]);
        } else if ($degree && !$fach) {
            $users = UserStudyCourse::findBySql('abschluss_id = :abschluss_id', [':abschluss_id' => $degree->id]);
        } else {
            $users = UserStudyCourse::findBySql('fach_id = :fach_id AND abschluss_id = :abschluss_id',
                [':fach_id' => $fach->id, ':abschluss_id' => $degree->id]
            );
        }
        if (empty($users)) {
            PageLayout::postError(_('Keine Studierenden zu den gewählten Angaben gefunden'));
            $this->redirect($this->indexURL());
            return;
        }

        $_SESSION['sms_data']['p_rec'] = SimpleCollection::createFromArray(
            SimpleCollection::createFromArray($users)->pluck('user')
        )->pluck('username');

        $subject = sprintf(
            _('Information zum Studiengang: %s %s'),
            $fach ? $fach->name: '' , $degree ? $degree->name : ''
        );

        $this->redirect(URLHelper::getURL('dispatch.php/messages/write',
            ['default_subject' => $subject, 'emailrequest' => 1]
        ));
    }

    private function createSidebar($view = 'subject' )
    {
        $widget = new ViewsWidget();
        $widget->addLink(_('Gruppieren nach Fächern'), $this->indexURL())
                ->setActive($view === 'subject');
        $widget->addLink(_('Gruppieren nach Abschlüssen'), $this->degreeURL())
                ->setActive($view === 'degrees');
        Sidebar::Get()->addWidget($widget);
    }
}