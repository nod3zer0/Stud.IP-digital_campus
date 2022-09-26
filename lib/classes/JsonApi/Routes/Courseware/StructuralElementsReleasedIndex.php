<?php

namespace JsonApi\Routes\Courseware;

use Courseware\StructuralElement;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\JsonApiController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Class StructuralElementsReleasedIndex.
 */
class StructuralElementsReleasedIndex extends JsonApiController
{
    protected $allowedPagingParameters = ['offset', 'limit'];

    protected $allowedIncludePaths = [
        'ancestors',
        'children',
        'containers',
        'containers.blocks',
        'containers.blocks.edit-blocker',
        'containers.blocks.editor',
        'containers.blocks.owner',
        'containers.blocks.user-data-field',
        'containers.blocks.user-progress',
        'course',
        'editor',
        'owner',
        'parent',
    ];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(Request $request, Response $response, $args)
    {
        $user = $this->getUser($request);
        if (!Authority::canIndexStructuralElementsReleased($user)) {
            throw new AuthorizationFailedException();
        }

        list($offset, $limit) = $this->getOffsetAndLimit();
        $resources = [];
        $contents = StructuralElement::findBySQL(
            'range_id = ? AND range_type = ? ORDER BY mkdate DESC',
            [$user->id, 'user']
        );

        foreach ($contents as $content) {
            if ((count($content->read_approval) && count($content->read_approval['users']) > 0) || (count($content->write_approval) && count($content->write_approval['users']) > 0)) {
                $resources[] = $content;
            }
        }

        return $this->getPaginatedContentResponse($resources, count($resources));
    }
}
