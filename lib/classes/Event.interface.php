<?php

/*
 * Event.interface.php - An interface for calendar events.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 */


/**
 * The Event interface represents calendar events.
 */
interface Event
{
    /**
     * Retrieves events that lie in a given time range.
     *
     * @param DateTime $begin The beginning of the time range.
     *
     * @param DateTime $end The end of the time range.
     *
     * @param string $range_id The range for which to get the events. This may be a user-ID,
     *     course-ID or another kind of ID.
     *
     * @return Event[] An array with event objects.
     */
    public static function getEvents(DateTime $begin, DateTime $end, string $range_id) : array;

    /**
     * Returns the ID of the event. This is the ID that is only
     * valid inside of Stud.IP.
     *
     * @return string The ID of the event object.
     */
    public function getObjectId() : string;

    /**
     * Returns the ID of the primary object where this object is linked to
     * in a primary-secondary relationship where this object is a secondary object.
     *
     * Example: A course date is a secondary object and the course it belongs to
     * is the primary object.
     *
     * @return string The ID of the primary object or an empty string if the
     *     implementation of the Event interface is a class of primary objects.
     */
    public function getPrimaryObjectID() : string;

    /**
     * Returns the class of the Event implementation.
     *
     * @return string The class name of the Event instance.
     */
    public function getObjectClass() : string;

    /**
     * Returns the title of this event.
     * If the user has not the permission Event::PERMISSION_READABLE,
     * the title is "Keine Berechtigung.".
     *
     * @return string The title of the event.
     */
    public function getTitle() : string;

    /**
     * Returns the start time of the event.
     *
     * @return DateTime The start time of the event.
     */
    public function getBegin() : DateTime;

    /**
     * Returns the end time of the event.
     *
     * @return DateTime The end time of the event.
     */
    public function getEnd() : DateTime;

    /**
     * Returns the duration of the event.
     *
     * @return DateInterval The duration of the event.
     */
    public function getDuration() : DateInterval;

    /**
     * Returns the location where the event takes place, if applicable.
     *
     * @return string The location of the event.
     */
    public function getLocation() : string;

    /**
     * Returns the global unique id of the event.
     *
     * @return string The global unique id of the event.
     */
    public function getUniqueId() : string;

    /**
     * Returns the description of the event.
     *
     * @return string The description of the event.
     */
    public function getDescription() : string;

    /**
     * Returns additional descriptions of the Event object.
     * These are specific for each implementation.
     *
     * @return array Additional descriptions for the Event implementation.
     *     Each array key represents a heading for the description and the
     *     value contains the description itself as plain text.
     *     In case this is not applicable for the implementation,
     *     an empty array is returned.
     */
    public function getAdditionalDescriptions() : array;

    /**
     * Returns whether the event is an all day event or not.
     *
     * @return bool True, if the event is an all day event, false otherwise.
     */
    public function isAllDayEvent() : bool;

    /**
     * Determines whether the specified user has write permissions for the event.
     *
     * @param string $user_id The user for which to check write permissions.
     *
     * @return bool True, if the user has write permissions, false otherwise.
     */
    public function isWritable(string $user_id) : bool;

    /**
     * Returns the creation date of the event.
     *
     * @return DateTime The creation date of the event.
     */
    public function getCreationDate() : DateTime;

    /**
     * Returns the modification date of the event.
     *
     * @return DateTime The modification date of the event.
     */
    public function getModificationDate() : DateTime;

    /**
     * Returns the import date of the event.
     *
     * @return DateTime The import date of the event.
     */
    public function getImportDate() : DateTime;

    /**
     * Returns the author of this event as user object.
     *
     * @return User|null The user object of the author of the event, if available.
     */
    public function getAuthor() : ?User;

    /**
     * Returns the editor of this event as user object.
     *
     * @return User|null The user object of the editor of the event, if available.
     */
    public function getEditor() : ?User;

    /**
     * Returns a JSON-encoded fullcalendar event object that represents the event.
     *
     * @param $user_id string The user for which to generate the fullcalendar event.
     *
     * @return \Studip\Calendar\EventData The EventData representation of the event.
     */
    public function toEventData(string $user_id) : \Studip\Calendar\EventData;
}
