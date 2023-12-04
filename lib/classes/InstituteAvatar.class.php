<?php
/**
 * This class represents the avatar of a institute.
 *
 * @author    André Noack <noack@data-quest.de>
 * @author    Marcus Lunzenauer <mlunzena@uos>
 * @copyright (c) Authors
 * @license   GPL2 or any later version
 * @since     1.10
 */
class InstituteAvatar extends CourseAvatar
{
    public const AVATAR_TYPE = 'institute';

    /**
     * Returns the CSS class to use for this avatar image.
     *
     * @param string  $size one of the constants Avatar::(NORMAL|MEDIUM|SMALL)
     * @return string CSS class to use for the avatar
     */
    protected function getCssClass($size)
    {
        return "institute-avatar-{$size} institute-{$this->user_id}";
    }

    /**
     * Return the default title of the avatar.
     * @return string the default title
     */
    public function getDefaultTitle()
    {
        $institute = Institute::find($this->user_id);
        return $institute ? (string) $institute->name : self::NOBODY;
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
