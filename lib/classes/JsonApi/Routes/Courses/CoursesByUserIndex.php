<?php

namespace JsonApi\Routes\Courses;

use Course;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Semester;
use User;

class CoursesByUserIndex extends JsonApiController
{
    protected $allowedIncludePaths = [
        'blubber-threads',
        'end-semester',
        'events',
        'feedback-elements',
        'file-refs',
        'folders',
        'forum-categories',
        'institute',
        'memberships',
        'news',
        'participating-institutes',
        'sem-class',
        'sem-type',
        'start-semester',
        'status-groups',
        'wiki-pages',
    ];

    protected $allowedPagingParameters = ['offset', 'limit'];

    protected $allowedFilteringParameters = ['semester'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        if (!$user = User::find($args['id'])) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canIndexMembershipsOfUser($this->getUser($request), $user)) {
            throw new AuthorizationFailedException();
        }

        if ($error = $this->validateFilters()) {
            throw new BadRequestException($error);
        }

        $courses = $this->findCoursesByUser(
            $user,
            $this->getSemesterFilter()
        );
        list($offset, $limit) = $this->getOffsetAndLimit();

        return $this->getPaginatedContentResponse(
            array_slice($courses, $offset, $limit),
            count($courses)
        );
    }

    private function validateFilters()
    {
        $filtering = $this->getQueryParameters()->getFilteringParameters() ?: [];

        // semester
        if (isset($filtering['semester'])) {
            if (!Semester::exists($filtering['semester'])) {
                return 'Invalid "semester".';
            }
        }
    }

    private function getSemesterFilter(): ?Semester
    {
        $filtering = $this->getQueryParameters()->getFilteringParameters();

        if (!isset($filtering['semester'])) {
            return null;
        }

        return Semester::find($filtering['semester']);
    }


    /**
     * @param User $user
     * @param Semester|null $semester
     *
     * @return Course[]
     */
    private function findCoursesByUser(User $user, ?Semester $semester): array
    {
        $courses = Course::findMany(
            $user->course_memberships->pluck('seminar_id'),
            'ORDER BY start_time, name'
        );

        if ($semester) {
            $courses = array_filter($courses, function (Course $course) use ($semester): bool {
                return $course->isInSemester($semester);
            });
        }

        return $courses;
    }
}
