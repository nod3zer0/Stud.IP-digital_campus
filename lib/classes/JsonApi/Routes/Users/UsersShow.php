<?php

namespace JsonApi\Routes\Users;

use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use JsonApi\Schemas\User as UserSchema;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;

class UsersShow extends JsonApiController
{
    protected $allowedIncludePaths = [
        UserSchema::REL_ACTIVITYSTREAM,
        UserSchema::REL_CONTACTS,
        UserSchema::REL_COURSES,
        UserSchema::REL_COURSE_MEMBERSHIPS,
        UserSchema::REL_EVENTS,
        UserSchema::REL_INSTITUTE_MEMBERSHIPS,
        UserSchema::REL_SCHEDULE,
    ];

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(Request $request, Response $response, array $args)
    {
        $routeName = RouteContext::fromRequest($request)
            ->getRoute()
            ->getName();
        if ($routeName === 'get-myself') {
            $observedUser = $this->getUser($request);
        } else {
            $observedUser = \User::find($args['id']);
        }
        if (!$observedUser) {
            throw new RecordNotFoundException();
        }

        if (!Authority::canShowUser($this->getUser($request), $observedUser)) {
            // absichtlich keine AuthorizationFailedException
            // damit unsichtbare Nutzer nicht ermittelt werden können
            throw new RecordNotFoundException();
        }

        return $this->getContentResponse($observedUser);
    }
}
