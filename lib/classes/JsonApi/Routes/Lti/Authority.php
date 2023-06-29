<?php

namespace JsonApi\Routes\Lti;

use LtiTool;
use User;

class Authority
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function canShowLtiTool(User $user, LtiTool $tool): bool
    {
        return true;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function canIndexLtiTools(User $user): bool
    {
        return true;
    }
}
