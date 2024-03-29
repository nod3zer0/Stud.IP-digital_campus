<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Block;
use Courseware\BlockComment;
use Courseware\BlockFeedback;
use Courseware\Clipboard;
use Courseware\Container;
use Courseware\Instance;
use Courseware\StructuralElement;
use Courseware\StructuralElementComment;
use Courseware\StructuralElementFeedback;
use Courseware\Task;
use Courseware\TaskFeedback;
use Courseware\TaskGroup;
use Courseware\Template;
use Courseware\Unit;
use Courseware\UserDataField;
use Courseware\UserProgress;
use Courseware\PublicLink;
use User;
use Course;

/**
 * @SuppressWarnings(PHPMD.CamelCaseParameterName)
 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.Superglobals)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Authority
{
    public static function canShowCoursewareInstance(User $user, Instance $resource)
    {
        return self::canShowStructuralElement($user, $resource->getRoot());
    }

    public static function canUpdateCoursewareInstance(User $user, Instance $resource)
    {
        return self::canUpdateStructuralElement($user, $resource->getRoot());
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function canShowBlock(User $user, Block $resource)
    {
        $struct = $resource->container->structural_element;

        return $struct->canRead($user);
    }

    public static function canIndexBlocks(User $user, Container $resource)
    {
        return self::canShowContainer($user, $resource);
    }

    public static function canCreateBlocks(User $user, Container $resource)
    {
        return self::canUpdateContainer($user, $resource);
    }

    public static function canUpdateBlock(User $user, Block $resource)
    {
        if ($resource->isBlocked()) {
            $structural_element = $resource->container->structural_element;

            if ($structural_element->range_type === 'user') {
                if ($structural_element->range_id === $user->id) {
                    return true;
                }

                return $structural_element->canEdit($user);
            }

            $perm = $structural_element->hasEditingPermission($user);

            return $resource->getBlockerUserId() === $user->id || $perm;
        }

        return self::canUpdateContainer($user, $resource->container);
    }

    public static function canDeleteBlock(User $user, Block $resource)
    {
        return self::canUpdateBlock($user, $resource);
    }

    public static function canUpdateEditBlocker(User $user, $resource)
    {
        $structural_element = null;
        if ($resource instanceof Block) {
            $structural_element = $resource->container->structural_element;
        }
        if ($resource instanceof Container) {
            $structural_element = $resource->structural_element;
        }
        if ($resource instanceof StructuralElement) {
            $structural_element = $resource;
        }

        if ($structural_element === null) {
            return false;
        }

        if ($structural_element->range_type === 'user') {
            if ($structural_element->range_id === $user->id) {
                return true;
            }

            return $structural_element->canEdit($user);
        }

        $perm = $structural_element->hasEditingPermission($user);

        return $resource->edit_blocker_id == '' || $resource->edit_blocker_id === $user->id || $perm;
    }

    public static function canShowContainer(User $user, Container $resource)
    {
        return self::canShowStructuralElement($user, $resource->getStructuralElement());
    }

    public static function canIndexContainers(User $user, StructuralElement $resource)
    {
        return self::canShowStructuralElement($user, $resource);
    }

    public static function canCreateContainer(User $user, StructuralElement $resource)
    {
        return self::canUpdateStructuralElement($user, $resource);
    }

    public static function canUpdateContainer(User $user, Container $resource)
    {
        return self::canUpdateStructuralElement($user, $resource->getStructuralElement());
    }

    public static function canDeleteContainer(User $user, Container $resource)
    {
        return self::canUpdateStructuralElement($user, $resource->getStructuralElement());
    }

    public static function canReorderBlocks(User $user, Container $resource)
    {
        return self::canUpdateContainer($user, $resource);
    }

    public static function canReorderContainers(User $user, StructuralElement $resource)
    {
        return self::canUpdateStructuralElement($user, $resource);
    }

    public static function canShowStructuralElement(User $user, StructuralElement $resource)
    {
        return $resource->canRead($user);
    }

    public static function canUpdateStructuralElement(User $user, StructuralElement $resource)
    {
        return $resource->canEdit($user);
    }

    public static function canCreateStructuralElement(User $user, StructuralElement $resource)
    {
        return self::canUpdateStructuralElement($user, $resource);
    }

    public static function canDeleteStructuralElement(User $user, StructuralElement $resource)
    {
        return self::canUpdateStructuralElement($user, $resource);
    }

    public static function canIndexBookmarks(User $user, Instance $resource)
    {
        return self::canShowCoursewareInstance($user, $resource);
    }

    public static function canUpdateBookmarks(User $user, Instance $resource)
    {
        return self::canShowCoursewareInstance($user, $resource);
    }

    public static function canAddBookmarkToAUser(User $actor, User $user)
    {
        return $actor->id === $user->id;
    }

    public static function canModifyBookmarksOfAUser(User $actor, User $user)
    {
        return $actor->id === $user->id;
    }

    public static function canIndexBookmarksOfAUser(User $actor, User $user)
    {
        return $actor->id === $user->id;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function canIndexStructuralElements(User $user)
    {
        return $GLOBALS['perm']->have_perm('root', $user->id);
    }

    public static function canIndexStructuralElementsShared(User $user)
    {
        //TODO ?
        return true;
    }

    public static function canIndexStructuralElementsReleased(User $user)
    {
        //TODO ?
        return true;
    }

    public static function canReorderStructuralElements(User $user, $resource)
    {
        return self::canUpdateStructuralElement($user, $resource);
    }

    public static function canShowUserDataField(User $user, UserDataField $resource)
    {
        return $user->id === $resource->user_id;
    }

    public static function canUpdateUserDataField(User $user, UserDataField $resource)
    {
        return $user->id == $resource->user_id;
    }

    public static function canShowUserProgress(User $user, UserProgress $resource)
    {
        return $user->id == $resource->user_id;
    }

    public static function canUpdateUserProgress(User $user, UserProgress $resource)
    {
        return $user->id == $resource->user_id;
    }

    public static function canIndexBlockComments(User $user, Block $resource)
    {
        return self::canShowBlock($user, $resource);
    }

    public static function canShowBlockComment(User $user, BlockComment $resource)
    {
        return self::canShowBlock($user, $resource);
    }

    public static function canCreateBlockComment(User $user, Block $resource)
    {
        return self::canShowBlock($user, $resource);
    }

    public static function canUpdateBlockComment(User $user, BlockComment $resource)
    {
        $perm = $resource->block->container->structural_element->hasEditingPermission($user);
        return $user->id === $resource->user_id || $perm;
    }

    public static function canDeleteBlockComment(User $user, BlockComment $resource)
    {
        return self::canUpdateBlockComment($user, $resource);
    }

    public static function canIndexBlockFeedback(User $user, Block $resource)
    {
        return self::canUpdateStructuralElement($user, $resource->container->structural_element);
    }

    public static function canCreateBlockFeedback(User $user, Block $resource)
    {
        return self::canShowBlock($user, $resource);
    }

    public static function canShowBlockFeedback(User $user, BlockFeedback $resource)
    {
        return $resource->user_id === $user->id || self::canUpdateBlock($user, $resource->block);
    }

    public static function canUpdateBlockFeedback(User $user, BlockFeedback $resource)
    {
        return self::canShowBlockFeedback($user, $resource);
    }

    public static function canDeleteBlockFeedback(User $user, BlockFeedback $resource)
    {
        return self::canUpdateBlockFeedback($user, $resource);
    }

    public static function canUploadStructuralElementsImage(User $user, StructuralElement $resource)
    {
        return self::canUpdateStructuralElement($user, $resource);
    }

    public static function canDeleteStructuralElementsImage(User $user, StructuralElement $resource)
    {
        return self::canUploadStructuralElementsImage($user, $resource);
    }

    public static function canShowTaskGroup(User $user, TaskGroup $resource): bool
    {
        return $resource['lecturer_id'] === $user->id;
    }

    public static function canUpdateTaskGroup(User $user, TaskGroup $resource): bool
    {
        return self::canCreateTasks($user, $resource->target);
    }

    public static function canDeleteTaskGroup(User $user, TaskGroup $resource): bool
    {
        return self::canUpdateTaskGroup($user, $resource);
    }

    public static function canShowTask(User $user, Task $resource): bool
    {
        return self::canUpdateTask($user, $resource);
    }

    public static function canIndexTasks(User $user): bool
    {
        // TODO: filtered index permissions are handled in the route
        return $GLOBALS['perm']->have_perm('root', $user->id);
    }

    public static function canCreateTasks(User $user, StructuralElement $resource): bool
    {
        return $resource->hasEditingPermission($user);
    }

    public static function canUpdateTask(User $user, Task $resource): bool
    {
        return $resource->canUpdate($user);
    }

    public static function canDeleteTask(User $user, Task $resource): bool
    {
        return self::canCreateTasks($user, $resource->structural_element) && !$resource->userIsASolver($user);
    }

    public static function canRenewTask(User $user, Task $resource): bool
    {
        return self::canDeleteTask($user, $resource);
    }

    public static function canCreateTaskFeedback(User $user, Task $resource): bool
    {
        return self::canCreateTasks($user, $resource->structural_element);
    }

    public static function canShowTaskFeedback(User $user, Task $resource): bool
    {
        return self::canShowTask($user, $resource);
    }

    public static function canUpdateTaskFeedback(User $user, Task $resource): bool
    {
        return self::canCreateTaskFeedback($user, $resource);
    }

    public static function canDeleteTaskFeedback(User $user, Task $resource): bool
    {
        return self::canCreateTaskFeedback($user, $resource);
    }

    public static function canIndexStructuralElementComments(User $user, StructuralElement $resource)
    {
        return self::canShowStructuralElement($user, $resource);
    }

    public static function canShowStructuralElementComment(User $user, StructuralElementComment $resource)
    {
        return self::canShowStructuralElement($user, $resource->structural_element);
    }

    public static function canCreateStructuralElementComment(User $user, StructuralElement $resource)
    {
        return self::canShowStructuralElement($user, $resource);
    }

    public static function canUpdateStructuralElementComment(User $user, StructuralElementComment $resource)
    {
        if ($GLOBALS['perm']->have_perm('root')) {
            return true;
        }

        $perm = $resource->structural_element->hasEditingPermission($user);

        return $user->id == $resource->user_id || $perm;
    }

    public static function canDeleteStructuralElementComment(User $user, StructuralElementComment $resource)
    {
        return self::canUpdateStructuralElementComment($user, $resource);
    }

    public static function canIndexStructuralElementFeedback(User $user, StructuralElement $resource)
    {
        return self::canUpdateStructuralElement($user, $resource);
    }

    public static function canCreateStructuralElementFeedback(User $user, StructuralElement $resource)
    {
        if ($GLOBALS['perm']->have_perm('root')) {
            return true;
        }

        $perm = $resource->hasEditingPermission($user);

        return $perm;
    }

    public static function canUpdateStructuralElementFeedback(User $user, StructuralElementFeedback $resource)
    {
        return self::canCreateStructuralElementFeedback($user, $resource->structural_element);
    }

    public static function canShowStructuralElementFeedback(User $user, StructuralElementFeedback $resource)
    {
        return $resource->user_id === $user->id ||
            self::canUpdateStructuralElement($user, $resource->structural_element);
    }

    public static function canDeleteStructuralElementFeedback(User $user, StructuralElementFeedback $resource)
    {
        return self::canUpdateStructuralElementFeedback($user, $resource);
    }

    public static function canShowTemplate(User $user, Template $resource)
    {
        // templates are for everybody, aren't they?
        return true;
    }

    public static function canIndexTemplates(User $user)
    {
        // templates are for everybody, aren't they?
        return true;
    }

    public static function canCreateTemplate(User $user)
    {
        return $GLOBALS['perm']->have_perm('admin', $user->id);
    }

    public static function canUpdateTemplate(User $user, Template $resource)
    {
        return self::canCreateTemplate($user);
    }

    public static function canDeleteTemplate(User $user, Template $resource)
    {
        return self::canCreateTemplate($user);
    }

    public static function canIndexPublicLinks(User $user): bool
    {
        return self::canCreatePublicLink($user);
    }

    public static function canShowPublicLink(User $user, PublicLink $resource): bool
    {
        return self::canUpdatePublicLink($user, $resource);
    }

    public static function canCreatePublicLink(User $user): bool
    {
        return true;
    }

    public static function canUpdatePublicLink(User $user, PublicLink $resource): bool
    {
        return $resource->user_id === $user->id;
    }

    public static function canDeletePublicLink(User $user, PublicLink $resource): bool
    {
        return self::canUpdatePublicLink($user, $resource);
    }

    public static function canShowPublicStructuralElement(StructuralElement $resource): bool
    {
        $publicLink = PublicLink::findOneBySQL('structural_element_id = ?', [$resource->id]);

        return (bool) $publicLink;
    }

    public static function canShowUnit(User $user, Unit $resource): bool
    {
        return $resource->canRead($user);
    }

    public static function canIndexUnits(User $user): bool
    {
        return $GLOBALS['perm']->have_perm('root', $user->id);
    }

    public static function canCreateUnit(User $user, \Range $range): bool
    {
        if ($user->id === $range->id) {
            return true;
        }
        return $GLOBALS['perm']->have_studip_perm('tutor', $range->id, $user->id);
    }

    public static function canSortUnit(User $user, \Range $range): bool
    {
        return self::canCreateUnit($user, $range);
    }

    public static function canUpdateUnit(User $user, Unit $resource): bool
    {
        return $resource->canEdit($user);
    }

    public static function canDeleteUnit(User $user, Unit $resource): bool
    {
        return self::canUpdateUnit($user, $resource);
    }

    public static function canIndexUnitsOfACourse(User $user, Course $course): bool
    {
        return $GLOBALS['perm']->have_studip_perm('user', $course->id, $user->id);
    }

    public static function canIndexUnitsOfAUser(User $request_user, User $user): bool
    {
        return $request_user->id === $user->id;
    }

    public static function canShowClipboard(User $user, Clipboard $resource): bool
    {
        return $resource->user_id === $user->id;
    }

    public static function canIndexClipboardsOfAUser(User $request_user, User $user): bool
    {
        return $request_user->id === $user->id;
    }

    public static function canIndexClipboards(User $user): bool
    {
        return $GLOBALS['perm']->have_perm('root', $user->id);
    }

    public static function canCreateClipboard(User $user, $resource): bool
    {
        if ($resource instanceof StructuralElement) {
            $structural_element = $resource;
        } else {
            $structural_element = $resource->getStructuralElement();
        }

        return $structural_element->canEdit($user);
    }

    public static function canUpdateClipboard(User $user, Clipboard $resource): bool
    {
        return $resource->user_id === $user->id;
    }

    public static function canDeleteClipboard(User $user, Clipboard $resource): bool
    {
        return self::canUpdateClipboard($user, $resource);
    }

    public static function canDeleteClipboardsOfAUser(User $request_user, User $user): bool
    {
        return self::canIndexClipboardsOfAUser($request_user, $user);
    }

    public static function canInsertFromClipboard(User $user, Clipboard $resource)
    {
        return $resource->user_id === $user->id;
    }

}
