<?php

namespace JsonApi\Routes\Tree;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\NonJsonApiController;

class DetailsOfTreeNodeCourse extends NonJsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $course = \Course::find($args['id']);
        if (!$course) {
            throw new RecordNotFoundException();
        }

        // Get course dates in textual form
        $dates = \Seminar::GetInstance($args['id'])->getDatesHTML([
            'semester_id' => null,
            'show_room'   => true,
        ]);

        $data = [
            'semester' => $course->semester_text,
            'lecturers' => [],
            'admissionstate' => null,
            'dates' => $dates
        ];

        // Get lecturers
        $lecturers = \SimpleCollection::createFromArray(
            \CourseMember::findByCourseAndStatus($args['id'], 'dozent')
        )->orderBy('position, nachname, vorname');
        foreach ($lecturers as $l) {
            $data['lecturers'][] = [
                'id' => $l->user_id,
                'username' => $l->username,
                'name' => $l->getUserFullname()
            ];
        }

        // Get admission state indicator if necessary
        if (\Config::get()->COURSE_SEARCH_SHOW_ADMISSION_STATE) {
            switch (\GlobalSearchCourses::getStatusCourseAdmission($course->id, $course->admission_prelim)) {
                case 1:
                    $data['admissionstate'] = [
                        'icon' => 'decline-circle',
                        'role' => \Icon::ROLE_STATUS_YELLOW,
                        'info' => _('Eingeschränkter Zugang')
                    ];
                    break;
                case 2:
                    $data['admissionstate'] = [
                        'icon' => 'decline-circle',
                        'role' => \Icon::ROLE_STATUS_RED,
                        'info' => _('Kein Zugang')
                    ];
                    break;
                default:
                    $data['admissionstate'] = [
                        'icon' => 'check-circle',
                        'role' => \Icon::ROLE_STATUS_GREEN,
                        'info' => _('Uneingeschränkter Zugang')
                    ];
            }

        }

        $response->getBody()->write(json_encode($data));

        return $response->withHeader('Content-type', 'application/json');
    }
}
