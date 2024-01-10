<?php

namespace JsonApi\Routes\Feedback;

use Feedback;
use FeedbackElement;
use FeedbackEntry;
use FeedbackRange;
use SimpleORMap;
use User;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Authority
{
    public static function canShowFeedbackElement(User $user, FeedbackElement $resource): bool
    {
        return Feedback::hasRangeAccess($resource->range_id, $resource->range_type, $user->getId());
    }

    public static function canIndexFeedbackEntries(User $user, FeedbackElement $resource): bool
    {
        return self::canShowFeedbackElement($user, $resource);
    }

    public static function canSeeResultsOfFeedbackElement(User $user, FeedbackElement $resource): bool
    {
        return self::canIndexFeedbackEntries($user, $resource) &&
            ($resource['results_visible'] || \Feedback::hasAdminPerm($resource['course_id'], $user->getId()));
    }

    public static function canIndexFeedbackElementsOfCourse(User $user, \Course $course): bool
    {
        return \Feedback::hasRangeAccess($course->getId(), \Course::class, $user->getId());
    }

    public static function canIndexFeedbackElementsOfFileRef(User $user, \FileRef $fileRef): bool
    {
        return \Feedback::hasRangeAccess($fileRef->getId(), \FileRef::class, $user->getId());
    }

    public static function canIndexFeedbackElementsOfFolder(User $user, \Folder $folder): bool
    {
        return \Feedback::hasRangeAccess($folder->getId(), \Folder::class, $user->getId());
    }

    public static function canShowFeedbackEntry(User $user, \FeedbackEntry $resource): bool
    {
        $feedbackElement = $resource->feedback;

        return self::canShowFeedbackElement($user, $feedbackElement);
    }

    public static function canCreateFeedbackEntry(User $user, FeedbackElement $element): bool
    {
        return $element->isFeedbackable($user->id);
    }

    public static function canUpdateFeedbackEntry(User $user, FeedbackEntry $entry): bool
    {
        return $entry->isEditable($user->id);
    }

    public static function canDeleteFeedbackEntry(User $user, FeedbackEntry $entry): bool
    {
        return $entry->isDeletable($user->id);
    }

    public static function canCreateFeedbackElement(User $user, FeedbackRange $range): bool
    {
        return $range->isRangeAccessible($user->id)
            && Feedback::hasCreatePerm($range->getRangeCourseId(), $user->id);
    }

    public static function canUpdateFeedbackElement(User $user, FeedbackElement $element): bool
    {
        $range = $element->getRange();

        return $range->isRangeAccessible($user->id)
            && Feedback::hasAdminPerm($range->getRangeCourseId(), $user->id);
    }

    public static function canDeleteFeedbackElement(User $user, FeedbackElement $element): bool
    {
        return self::canUpdateFeedbackElement($user, $element);
    }
}
