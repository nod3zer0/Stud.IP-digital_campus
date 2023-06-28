<?php

class TreeController extends AuthenticatedController
{
    public function export_csv_action()
    {
        if (!Request::isPost()) {
            throw new MethodNotAllowedException();
        }

        $ids = explode(',', Request::get('courses', ''));
        $courses = Course::findMany($ids);

        $captions = [
            _('Veranstaltungsnummer'),
            _('Name'),
            _('Semester'),
            _('Zeiten'),
            _('Lehrende')
        ];

        $data = [];
        foreach ($courses as $course) {
            $sem = Seminar::getInstance($course->id);
            $lecturers = SimpleCollection::createFromArray(
                CourseMember::findByCourseAndStatus($course->id, 'dozent')
            )->orderBy('position, nachname, vorname');

            $lecturersSorted = array_map(
                function ($l) {
                    return implode(', ', $l);
                },
                $lecturers->toArray('nachname vorname title_front title_rear')
            );

            $data[] = [
                $course->veranstaltungsnummer,
                $course->getFullname('type-number-name'),
                $course->getTextualSemester(),
                $sem->getDatesExport(),
                implode(', ', $lecturersSorted)
            ];
        }

        $tmpname = md5(uniqid('ErgebnisVeranstaltungssuche'));
        if (array_to_csv($data, $GLOBALS['TMP_PATH'] . '/' . $tmpname, $captions)) {
            $this->render_text(FileManager::getDownloadURLForTemporaryFile(
                $tmpname,
                'veranstaltungssuche.csv'
            ));
        } else {
            $this->set_status(400, 'The csv could not be created.');
        }
    }
}
