<?php
/**
 * This class represents the avatar of a course.
 *
 * @author    Marcus Lunzenauer (mlunzena@uos)
 * @copyright (c) Authors
 * @license GPL2 or any later version
 * @since     1.10
 */
class CourseAvatar extends Avatar
{
    public const AVATAR_TYPE = 'course';

    /**
     * Returns the CSS class to use for this avatar image.
     *
     * @param string  one of the constants Avatar::(NORMAL|MEDIUM|SMALL)
     *
     * @return string CSS class to use for the avatar
     */
    protected function getCssClass($size)
    {
        return "course-avatar-{$size} course-{$this->user_id}";
    }

    /**
     * Return the default title of the avatar.
     * @return string the default title
     */
    public function getDefaultTitle()
    {
        return Seminar::GetInstance($this->user_id)->name;
    }
    
    /**
     * Return if avatar is visible to the current user.
     * @return boolean: true if visible
     */
    protected function checkAvatarVisibility()
    {
        //no special conditions for visibility of course-avatars yet
        return true;
    }
}
