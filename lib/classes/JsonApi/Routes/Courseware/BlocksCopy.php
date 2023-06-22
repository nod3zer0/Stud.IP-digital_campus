<?php

namespace JsonApi\Routes\Courseware;

use JsonApi\NonJsonApiController;
use Courseware\Block;
use Courseware\Container;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Errors\UnprocessableEntityException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Copy a courseware block in a courseware container
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */

class BlocksCopy extends NonJsonApiController
{
    public function __invoke(Request $request, Response $response, array $args)
    {

        $data = $request->getParsedBody()['data'];

        $block = \Courseware\Block::find($data['block']['id']);
        if (!$block) {
            throw new RecordNotFoundException();
        }
        $container = \Courseware\Container::find($data['parent_id']);
        if (!$container) {
            throw new RecordNotFoundException();
        }
        $sectionIndex = $data['section'];
        $user = $this->getUser($request);

        if (!Authority::canCreateBlocks($user, $container) || !Authority::canUpdateBlock($user, $block)) {
            throw new AuthorizationFailedException();
        }

        $new_block = $this->copyBlock($user, $block, $container, $sectionIndex);

        $response = $response->withHeader('Content-Type', 'application/json');
        $response->getBody()->write((string) json_encode($new_block));

        return $response;
    }

    private function copyBlock(\User $user, \Courseware\Block $remote_block, \Courseware\Container $container, $sectionIndex)
    {
        return $remote_block->copy($user, $container, $sectionIndex);
    }
}
