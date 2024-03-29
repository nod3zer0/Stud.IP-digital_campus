<?php

namespace JsonApi\Routes\Forum;

use JsonApi\Models\ForumEntry;

class ForumAuthority
{
    public static function has(\User $user, $perm, \Course $course, ForumEntry $topic = null)
    {
        if (!\ForumPerm::has($perm, $course->id, $user->id)) {
            return false;
        }

        if ($topic) {
            try {
                \ForumPerm::checkTopicId($course->id, $topic->id);
            } catch (\AccessDeniedException $e) {
                return false;
            }
        }

        return true;
    }
}
