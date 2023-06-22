<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Clipboard;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Displays all clipboards of one user.
 * 
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.4
 */
class UsersClipboardsDelete extends JsonApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $user = \User::find($args['id']);
        if (!$user) {
            throw new RecordNotFoundException();
        }
        $request_user = $this->getUser($request);
        if (!Authority::canDeleteClipboardsOfAUser($request_user, $user)) {
            throw new AuthorizationFailedException();
        }

        Clipboard::deleteUsersClipboards($user, $args['type']);

        return  $this->getCodeResponse(204);
    }
}
