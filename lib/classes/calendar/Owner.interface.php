<?php

namespace Studip\Calendar;

/**
 * The Studip\Calendar\Owner interface defines methods that classes whose instances own calendars
 * shall implement to faciliate permission checks for that calendars.
 */
interface Owner
{
    /**
     * Retrieves the Owner object for a specified owner-ID.
     *
     * @param string $owner_id The ID of the owner.
     *
     * @return Owner|null Either the Owner object if it can be found or null in case
     *     it cannot be found.
     */
    public static function getCalendarOwner(string $owner_id) : ?Owner;

    /**
     * Determines whether the specified user has read permissions to the calendar.
     *
     * @param string|null $user_id The ID of the user for which to determine write permissions.
     *                             Defaults to the current user if no user-ID is provided.
     *
     * @return bool True, if the user has read permissions, false otherwise.
     */
    public function isCalendarReadable(?string $user_id = null) : bool;

    /**
     * Determines whether the specified user has write permissions to the calendar.
     *
     * @param string|null $user_id The ID of the user for which to determine write permissions.
     *                             Defaults to the current user if no user-ID is provided.
     *
     * @return bool True, if the user has write permissions, false otherwise.
     */
    public function isCalendarWritable(?string $user_id = null) : bool;
}
