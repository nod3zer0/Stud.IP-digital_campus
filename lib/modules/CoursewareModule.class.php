<?php

use Courseware\Instance;
use Courseware\StructuralElement;

class CoursewareModule extends CorePlugin implements SystemPlugin, StudipModule
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        NotificationCenter::on('CourseDidDelete', function ($event, $course) {
            $last_element_configs = \ConfigValue::findBySQL('field = ? AND value LIKE ?', [
                'COURSEWARE_LAST_ELEMENT',
                '%' . $course->id . '%',
            ]);
            foreach ($last_element_configs as $config) {
                $arr = json_decode($config->value, true);
                $arr = array_filter(
                    $arr,
                    function ($key) use ($course) {
                        return $key !== $course->id;
                    },
                    ARRAY_FILTER_USE_KEY
                );
                \UserConfig::get($config->range_id)->store('COURSEWARE_LAST_ELEMENT', $arr);
            }
        });
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getInfoTemplate($courseId)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabNavigation($courseId)
    {
        $navigation = new Navigation(
            _('Courseware'),
            URLHelper::getURL('dispatch.php/course/courseware/?cid='.$courseId)
        );
        $navigation->setImage(Icon::create('courseware', Icon::ROLE_INFO_ALT));
        $navigation->addSubNavigation(
            'shelf',
            new Navigation(_('Lernmaterialien'), 'dispatch.php/course/courseware/?cid=' . $courseId)
        );
        $navigation->addSubNavigation(
            'unit',
            new Navigation(_('Inhalt'), 'dispatch.php/course/courseware/courseware?cid=' . $courseId)
        );
        $navigation->addSubNavigation(
            'activities',
            new Navigation(_('Aktivitäten'), 'dispatch.php/course/courseware/activities?cid=' . $courseId)
        );
        $navigation->addSubNavigation(
            'tasks',
            new Navigation(_('Aufgaben'), 'dispatch.php/course/courseware/tasks?cid=' . $courseId)
        );
        $navigation->addSubNavigation(
            'comments',
            new Navigation(_('Kommentare und Feedback'), 'dispatch.php/course/courseware/comments_overview?cid=' . $courseId)
        );

        return ['courseware' => $navigation];
    }

    /**
     * {@inheritdoc}
     */
    public function getIconNavigation($courseId, $last_visit, $user_id)
    {
        $statement = DBManager::get()->prepare("
                SELECT COUNT(DISTINCT elem.id) 
                FROM `cw_structural_elements` AS elem 
                INNER JOIN `cw_containers` as container ON (elem.id = container.structural_element_id)
                INNER JOIN `cw_blocks` as blocks ON (container.id = blocks.container_id)
                WHERE elem.range_type = 'course' 
                AND elem.range_id = :range_id
                AND blocks.payload != ''
                AND blocks.chdate > :last_visit
                AND blocks.editor_id != :user_id
        ");

        $statement->execute([
            'range_id' => $courseId,
            'last_visit' => $last_visit,
            'user_id' => $user_id
        ]);

        $new = $statement->fetchColumn();

        $nav = new Navigation(_('Courseware'), 'dispatch.php/course/courseware');
        $nav->setImage(Icon::create('courseware', Icon::ROLE_CLICKABLE), [
            'title' => _('Courseware'),
        ]);

        if ($new > 0) {
            if ($new === 1) {
                $text =  _('neue Seite');

            } else {
                $text =  _('neue Seiten');
            }
            $nav->setImage(Icon::create('courseware', Icon::ROLE_ATTENTION), [
                'title' => $new . ' ' . $text,
            ]);
            $nav->setBadgeNumber("$new");
        }

        return $nav;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata()
    {
        return [
            'summary' => _('Lerninhalte erstellen, verteilen und erleben'),
            'description' => _('Mit Courseware können Sie interaktive, multimediale Lerninhalte erstellen und nutzen. '
                             . 'Die Lerninhalte lassen sich hierarchisch unterteilen und können aus Texten, '
                             . 'Videosequenzen, Aufgaben, Kommunikationselementen und einer Vielzahl weiterer '
                             . 'Elemente bestehen. Fertige Lerninhalte können exportiert und in andere Kurse oder '
                             . 'andere Installationen importiert werden. Courseware ist nicht nur für digitale '
                             . 'Formate geeignet, sondern kann auch genutzt werden, um klassische '
                             . 'Präsenzveranstaltungen mit Online-Anteilen zu ergänzen. Formate wie integriertes '
                             . 'Lernen (Blended Learning) lassen sich mit Courseware ideal umsetzen. Kollaboratives '
                             . 'Lernen kann dank Schreibrechtevergabe und dem Einsatz von Courseware in '
                             . 'Studiengruppen realisiert werden.'),
            'displayname' => _('Courseware'),
            'category' => _('Lehr- und Lernorganisation'),
            'icon' => Icon::create('courseware', 'info'),
            'screenshots' => [
                'path' => 'assets/images/plus/screenshots/Courseware',
                'pictures' => [
                    0 => ['source' => 'preview.png', 'title' => _('Überssichtsseite der Courseware')],
                ],
            ],
        ];
    }

    public function isActivatableForContext(Range $context)
    {
        return $context->getRangeType() === 'course';
    }
}
