<?php

namespace JsonApi\Routes\Courseware\Rel;

use Courseware\Bookmark;
use Courseware\Instance;
use Courseware\StructuralElement;
use JsonApi\Errors\AuthorizationFailedException;
use JsonApi\Errors\BadRequestException;
use JsonApi\Errors\ConflictException;
use JsonApi\Errors\RecordNotFoundException;
use JsonApi\Routes\Courseware\Authority;
use JsonApi\Routes\Courseware\CoursewareInstancesHelper;
use JsonApi\Routes\RelationshipsController;
use Psr\Http\Message\ServerRequestInterface as Request;

class UsersBookmarkedStructuralElements extends RelationshipsController
{
    use CoursewareInstancesHelper;

    protected $allowedPagingParameters = ['offset', 'limit'];

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function fetchRelationship(Request $request, $related)
    {
        $bookmarks = array_column(Bookmark::findUsersBookmarks($related), 'element');
        $total = count($bookmarks);
        list($offset, $limit) = $this->getOffsetAndLimit();
        $page = array_slice($bookmarks, $offset, $limit);

        return $this->getPaginatedIdentifiersResponse($page, $total);
    }

    protected function replaceRelationship(Request $request, $related)
    {
        $json = $this->validate($request);
        $structuralElements = $this->validateStructuralElements($user = $this->getUser($request), $json, $related);
        $this->replaceBookmarks($related, $structuralElements);

        return $this->getCodeResponse(204);
    }

    protected function addToRelationship(Request $request, $related)
    {
        $json = $this->validate($request);
        $structuralElements = $this->validateStructuralElements($user = $this->getUser($request), $json, $related);
        $this->addBookmarks($related, $structuralElements);

        return $this->getCodeResponse(204);
    }

    protected function removeFromRelationship(Request $request, $related)
    {
        $json = $this->validate($request);
        $structuralElements = $this->validateStructuralElements($user = $this->getUser($request), $json, $related);
        $this->removeBookmarks($user, $structuralElements);

        return $this->getCodeResponse(204);
    }

    protected function findRelated(array $args)
    {
        if (!($related = \User::find($args['id']))) {
            throw new RecordNotFoundException();
        }

        return $related;
    }

    protected function authorize(Request $request, $resource)
    {
        $observer = $this->getUser($request);
        $observed = $resource;
        switch ($request->getMethod()) {
            case 'GET':
                return Authority::canIndexBookmarksOfAUser($observer, $observed);

            case 'DELETE':
            case 'PATCH':
            case 'POST':
                return Authority::canModifyBookmarksOfAUser($observer, $observed);

            default:
                return false;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipSelfLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipSelfLink($resource, \JsonApi\Schemas\User::REL_COURSEWARE_BOOKMARKS);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function getRelationshipRelatedLink($resource, $schema, $userData)
    {
        return $schema->getRelationshipRelatedLink($resource, \JsonApi\Schemas\User::REL_COURSEWARE_BOOKMARKS);
    }

    protected function validateResourceDocument($json, $data)
    {
        if (!self::arrayHas($json, 'data')) {
            return 'Missing `data` member at document´s top level.';
        }

        $data = self::arrayGet($json, 'data');

        if (!is_array($data)) {
            return 'Document´s ´data´ must be an array.';
        }

        foreach ($data as $item) {
            if (\JsonApi\Schemas\Courseware\StructuralElement::TYPE !== self::arrayGet($item, 'type')) {
                return 'Wrong `type` in document´s `data`.';
            }

            if (!self::arrayGet($item, 'id')) {
                return 'Missing `id` of document´s `data`.';
            }
        }

        if (self::arrayHas($json, 'data.attributes')) {
            return 'Document must not have `attributes`.';
        }
    }

    private function validateStructuralElements(\User $actor, $json, \User $user)
    {
        $structuralElements = [];

        foreach (self::arrayGet($json, 'data') as $structuralElementResource) {
            if (!($structuralElement = StructuralElement::find($structuralElementResource['id']))) {
                throw new RecordNotFoundException();
            }

            if (!Authority::canModifyBookmarksOfAUser($actor, $user)) {
                throw new AuthorizationFailedException();
            }

            if (!Authority::canShowStructuralElement($user, $structuralElement)) {
                throw new RecordNotFoundException();
            }

            $structuralElements[] = $structuralElement->id;
        }

        return $structuralElements;
    }

    private function replaceBookmarks(\User $user, array $newIds)
    {
        $oldIds = array_column(Bookmark::findUsersBookmarks($user), 'element_id');
        $onlyInOld = array_diff($oldIds, $newIds);
        $onlyInNew = array_diff($newIds, $oldIds);

        $this->removeBookmarks($user, $onlyInOld);
        $this->addBookmarks($user, $onlyInNew);
    }

    private function addBookmarks(\User $user, array $newIds): void
    {
        foreach ($newIds as $structuralElementId) {
            if (Bookmark::countBySQL('user_id = ? AND element_id = ?', [$user->id, $structuralElementId])) {
                continue;
            }
            Bookmark::create(['user_id' => $user->id, 'element_id' => $structuralElementId]);
        }
    }

    private function removeBookmarks(\User $user, array $oldIds): void
    {
        Bookmark::deleteBySQL('user_id = ? AND element_id IN (?)', [$user->id, $oldIds]);
    }
}
