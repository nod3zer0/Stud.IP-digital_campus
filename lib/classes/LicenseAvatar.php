<?php

/*
 * Copyright (C) 2020 - Rasmus Fuhse
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 */


/**
 * This class represents the image-picture of a license.
 */
class LicenseAvatar extends Avatar
{
    public const AVATAR_TYPE = 'licenses';
    protected const CREATE_CHUNKED_FOLDERS = false;

    public function getImageTag($size = Avatar::MEDIUM, $opt = [])
    {
        if (!$this->is_customized()) {
            return "";
        }

        return parent::getImageTag($size, $opt);
    }

    /**
     * Returns the CSS class to use for this avatar image.
     *
     * @param string  $size one of the constants Avatar::(NORMAL|MEDIUM|SMALL)
     * @return string CSS class to use for the avatar
     */
    protected function getCssClass($size)
    {
        return sprintf(
            'license-avatar-%s license-%s',
            $size,
            $this->user_id
        );
    }

    /**
     * Return the default title of the avatar.
     * @return string the default title
     */
    public function getDefaultTitle()
    {
        return License::find($this->user_id)->name;
    }

    /**
     * Return if avatar is visible to the current user.
     * @return boolean: true if visible
     */
    protected function checkAvatarVisibility()
    {
        return true;
    }

    /**
     * Return the dimension of a size
     *
     * @param  string $size the dimension of a size
     * @return array a tupel of integers [width, height]
     */
    public static function getDimension($size)
    {
        $dimensions = [
            Avatar::NORMAL => [300, 100],
            Avatar::MEDIUM => [120, 40],
            Avatar::SMALL  => [60, 20]
        ];
        return $dimensions[$size];
    }
}
