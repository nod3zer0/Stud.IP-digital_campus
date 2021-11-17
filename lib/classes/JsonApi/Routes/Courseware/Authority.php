<?php

namespace JsonApi\Routes\Courseware;

use Courseware\Block;
use Courseware\BlockComment;
use Courseware\BlockFeedback;
use Courseware\Container;
use Courseware\Instance;
use Courseware\StructuralElement;
use Courseware\UserDataField;
use Courseware\UserProgress;
use User;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
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
            return $resource->getBlockerUserId() == $user->id;
        }

        return self::canUpdateContainer($user, $resource->container);
    }

    public static function canDeleteBlock(User $user, Block $resource)
    {
        return self::canUpdateBlock($user, $resource);
    }

    public static function canUpdateEditBlocker(User $user, $resource)
    {
        return $resource->edit_blocker_id == '' || $resource->edit_blocker_id === $user->id;
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

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function canIndexStructuralElements(User $user)
    {
        return $GLOBALS['perm']->have_perm('root', $user->id);
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
        return $user->id == $resource->user_id;
        // should dozent be able to update?
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

    public static function canUploadStructuralElementsImage(User $user, StructuralElement $resource)
    {
        return self::canUpdateStructuralElement($user, $resource);
    }

    public static function canDeleteStructuralElementsImage(User $user, StructuralElement $resource)
    {
        return self::canUploadStructuralElementsImage($user, $resource);
    }
}
