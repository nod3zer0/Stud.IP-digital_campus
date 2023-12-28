<?php
/**
 * ImportIliasTestresults
 *
 * @author André Noack <noack@data-quest.de>, Suchi & Berg GmbH <info@data-quest.de>
 */

class ImportIliasTestresults extends CronJob
{
    public static function getName()
    {
        return _('Testergebnisse aus ILIAS importieren');
    }

    public static function getDescription()
    {
        return _('Importiert Testergebnisse in das Gradebook');
    }

    public static function getParameters()
    {
        return [
            'verbose' => [
                'type'        => 'boolean',
                'default'     => false,
                'status'      => 'optional',
                'description' => _('Sollen Ausgaben erzeugt werden (sind später im Log des Cronjobs sichtbar)'),
            ]
        ];
    }

    public function execute($last_result, $parameters = [])
    {
        $verbose = $parameters['verbose'];
        $db = DBManager::get();
        if (Config::get()->ILIAS_INTERFACE_ENABLE) {
            $courses = $db->fetchFirst("SELECT DISTINCT course_id FROM grading_definitions WHERE tool='ILIAS'");
            foreach ($courses as $course_id) {
                $course = Course::find($course_id);
                if ($course && $course->isToolActive('IliasInterfaceModule')) {
                    $num = IliasObjectConnections::importIliasResultsForCourse($course);
                    if ($verbose) {
                        echo 'Veranstaltung: ' . $course->name . ' '. $course->id . ': ' . $num . ' Ergebnisse übertragen.' . "\n";
                    }
                }
            }
        } else {
            echo 'ILIAS_INTERFACE is not enabled';
        }
    }
}
