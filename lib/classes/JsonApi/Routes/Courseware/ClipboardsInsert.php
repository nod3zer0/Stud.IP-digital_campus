<?php

namespace JsonApi\Routes\Courseware;

use JsonApi\NonJsonApiController;
use Courseware\Block;
use Courseware\Clipboard;
use Courseware\Container;
use Courseware\StructuralElement;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\UnprocessableEntityException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * 
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.4
 */

class ClipboardsInsert extends NonJsonApiController
{
    public function __invoke(Request $request, Response $response, array $args)
    {
        $data = $request->getParsedBody()['data'];
        $clipboard = Clipboard::find($args['id']);

        if (!$clipboard) {
            throw new RecordNotFoundException();
        }

        $user = $this->getUser($request);

        if (!Authority::canInsertFromClipboard($user, $clipboard)) {
            throw new AuthorizationFailedException();
        }

        $backup = json_decode($clipboard->backup);

        if ($clipboard->object_type === 'courseware-blocks') {
            $sectionIndex = $data['section'];
            $container = \Courseware\Container::find($data['parent_id']);
            if (!$container) {
                throw new RecordNotFoundException();
            }
            if (!Authority::canCreateBlocks($user, $container)) {
                throw new AuthorizationFailedException();
            }
            $object = Block::createFromData($user, $backup, $container, $sectionIndex);
        }

        if ($clipboard->object_type === 'courseware-containers') {
            $element = \Courseware\StructuralElement::find($data['parent_id']);
            if (!$element) {
                throw new RecordNotFoundException();
            }
            if (!Authority::canCreateContainer($user, $element)) {
                throw new AuthorizationFailedException();
            }
            $object = Container::createFromData($user, $backup, $element);
        }

        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write((string) json_encode($object));

        return $response;
    }
}
