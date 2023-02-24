<?php

namespace Studip\Forms;

class NewsRangesInput extends Input
{

    public function render()
    {
        $context = $this->getContextObject();
        $sql = "SELECT CONCAT(`Seminar_id`, '__seminar') AS `range_id`, `name` FROM `seminare` WHERE `name` LIKE :input ";
        if ($GLOBALS['perm']->have_perm('admin')) {
            $sql .= "UNION SELECT CONCAT(`Institut_id`, '__institute') AS `range_id`, `Name` AS `name` FROM Institute WHERE `name` LIKE :input ";
            if (!$GLOBALS['perm']->have_perm('root')) {
                $sql .= "AND ";
            }
        }
        if ($GLOBALS['perm']->have_perm('root')) {
            $sql .= "UNION SELECT * FROM (SELECT CAST('studip__home' AS BINARY) AS `range_id`, '"._('Stud.IP-Startseite')."' AS `name`) as tmp_global_table WHERE `name` LIKE :input ";
            $sql .= "UNION SELECT CONCAT(`user_id`, '__person') AS `range_id`, CONCAT(`Vorname`, ' ', `Nachname`) AS `name` FROM `auth_user_md5` WHERE CONCAT(`Vorname`, ' ', `Nachname`) LIKE :input ";
        } else {
            $sql .= "UNION SELECT * FROM (SELECT CAST('".\User::findCurrent()->id."__person' AS BINARY) AS `range_id`, '".\addslashes(\User::findCurrent()->getFullName()." - "._('Profilseite'))."' AS `name`) as tmp_user_table WHERE `name` LIKE :input ";
        }
        $searchtype = new \SQLSearch($sql, _('Bereich suchen'));
        $items = [];
        $icons = [
            'global' => 'home',
            'sem'    => 'seminar',
            'inst'   => 'institute',
            'user'   => 'person'
        ];
        foreach ($context->{$this->name} as $newsrange) {
            $items[] = [
                'value'     => $newsrange->range_id,
                'name'      => (string) $newsrange->name,
                'icon'      => $icons[$newsrange->type],
                'deletable' => \StudipNews::haveRangePermission('edit', $newsrange->range_id)
            ];
        }

        $selectable = [];

        // Stud.IP
        $studip_options = $this->getStudipOptions();
        if (count($studip_options) > 0) {
            $selectable[] = [
                'label' => _('Stud.IP'),
                'options' => $studip_options
            ];
        }

        // Institutes
        $inst_options = $this->getInstituteOptions();
        if (count($inst_options) > 0) {
            $selectable[] = [
                'label' => _('Einrichtungen'),
                'options' => $inst_options
            ];
        }

        // Courses
        $course_options = $this->getCourseOptions();
        if (count($course_options) > 0) {
            $selectable[] = [
                'label' => _('Veranstaltungen'),
                'options' => $course_options
            ];
        }

        $template = $GLOBALS['template_factory']->open('forms/news_ranges_input');
        $template->name = $this->name;
        $template->items = $items;
        $template->searchtype = $searchtype;
        $template->selectable = $selectable;
        $template->category_order = ['home', 'institute', 'seminar', 'person'];
        return $template->render();
    }

    public function getRequestValue()
    {
        $new_ranges = \Request::getArray($this->name);
        $context = $this->getContextObject();
        if ($context) {
            $options = $context->getRelationOptions($this->name);
            $old_ranges = array_map(function ($r) {
                return $r['range_id'];
            }, $context[$this->name]->getArrayCopy());

            foreach ($new_ranges as $index => $range_id) {
                if (!in_array($range_id, $old_ranges)) {
                    if (!\StudipNews::haveRangePermission('edit', $range_id)) {
                        unset($new_ranges[$index]);
                    }
                }
            }
            foreach ($old_ranges as $index => $range_id) {
                if (!in_array($range_id, $new_ranges)) {
                    if (!\StudipNews::haveRangePermission('edit', $range_id)) {
                        $new_ranges[] = $range_id;
                    }
                }
            }

            $class = $options['class_name'];
            return array_map(function ($id) use ($class, $context) {
                $range = new $class();
                $range['range_id'] = $id;
                if (!$context->id) {
                    $context->setId($context->getNewId());
                }
                $range['news_id'] = $context->id;
                return $range;
            }, $new_ranges);
        }
        return [];
    }

    /**
     * Returns a list of all possible stud.ip related ranges to link a news to.
     */
    public function getStudipOptions(): array
    {
        $options = [];

        if ($GLOBALS['perm']->have_perm('root')) {
            $options[] = [
                'value' => 'studip__home',
                'name'  => _('Stud.IP-Startseite'),
            ];
        }

        $options[] = [
            'value' => \User::findCurrent()->id . '__person',
            'name' => _('Meine Profilseite')
        ];

        return $options;
    }

    /**
     * Returns a list of all possible institute ranges to link a news to.
     */
    public function getInstituteOptions(): array
    {
        $options = [];

        foreach (\Institute::getMyInstitutes() as $institut) {
            if (!\StudipNews::haveRangePermission('edit', $institut['Institut_id'])) {
                continue;
            }

            $options[] = [
                'value' => $institut['Institut_id'] . '__institute',
                'name'  => (string) $institut['Name'],
            ];
        }

        return $options;
    }

    /**
     * Returns a list of all possible course ranges to link a news to.
     */
    protected function getCourseOptions(): array
    {
        if ($GLOBALS['perm']->have_perm('admin')) {
            return [];
        }

        $name_format = \Config::get()->IMPORANT_SEMNUMBER ? 'number-name-semester' : 'name-semester';

        $options = [];
        foreach (\Course::findByUser(\User::findCurrent()->id) as $course) {
            if (!\StudipNews::haveRangePermission('edit', $course->id)) {
                continue;
            }

            $options[] = [
                'value' => $course->id . '__seminar',
                'name'  => $course->getFullname($name_format),
            ];
        }

        return $options;
    }
}
