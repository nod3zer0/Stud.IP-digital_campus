<?php
/**
 * ProfileModel
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      David Siegfried <david.siegfried@uni-oldenburg.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.4
 */

class ProfileModel
{
    protected $perm;
    /**
     * Internal current selected user id
     * @var String
     */
    protected $current_user;

    /**
     * Internal current logged in user id
     * @var String
     */
    protected $user;

    /**
     * Internal user homepage visbilities
     * @var array
     */
    protected $visibilities;

    /**
     * Get informations on depending selected user
     * @param String $current_user
     * @param String $user
     */
    public function __construct($current_user, $user)
    {
        $this->current_user = User::find($current_user);
        $this->user         = User::find($user);
        $this->visibilities = $this->getHomepageVisibilities();
        $this->perm         = $GLOBALS['perm'];
    }

    /**
     * Get the homepagevisibilities
     *
     * @return array
     */
    public function getHomepageVisibilities()
    {
        $visibilities = get_local_visibility_by_id(
            $this->current_user ? $this->current_user->id : null,
            'homepage'
        );
        if (is_array(json_decode($visibilities, true))) {
            return json_decode($visibilities, true);
        }
        return [];
    }

    /**
     * Returns the visibility value
     *
     * @return String
     */
    public function getVisibilityValue($param, $visibility = '')
    {
        if (Visibility::verify($visibility ?: $param, $this->current_user->user_id)) {
            return $this->current_user->$param;
        }
        return false;
    }

    /**
     * Returns a specific value of the visibilies
     * @param String $param
     * @return String
     */

    public function getSpecificVisibilityValue($param)
    {
        if (!empty($this->visibilities[$param])) {
            return $this->visibilities[$param];
        }
        return false;
    }

    /**
     * Creates an array with all seminars
     *
     * @return array
     */
    public function getDozentSeminars()
    {
        $courses = [];
        $semester = [];
        $next_semester = Semester::findNext();
        $current_semester = Semester::findCurrent();
        $previous_semester = Semester::findPrevious();
        if ($next_semester) {
            $semester[$next_semester->id] = $next_semester;
        }
        if ($current_semester) {
            $semester[$current_semester->id] = $current_semester;
        }
        if ($previous_semester) {
            $semester[$previous_semester->id] = $previous_semester;
        }
        $field = 'name';
        if (Config::get()->IMPORTANT_SEMNUMBER) {
            $field = "veranstaltungsnummer,{$field}";
        }
        $allcourses = new SimpleCollection(Course::findBySQL("INNER JOIN seminar_user USING(Seminar_id) WHERE user_id=? AND seminar_user.status='dozent' AND seminare.visible=1", [$this->current_user->id]));
        foreach (array_filter($semester) as $one) {
            $courses[(string) $one->name] = $allcourses->filter(function ($c) use ($one) {
                if (Config::get()->HIDE_STUDYGROUPS_FROM_PROFILE && $c->isStudygroup()) {
                    return false;
                }
                if (!$c->isOpenEnded()) {
                    return $c->isInSemester($one);
                } elseif ($one->isCurrent()) {
                    return $c;
                }
                return false;
            })->orderBy($field);

            if (!$courses[(string) $one->name]->count()) {
                unset($courses[(string) $one->name]);
            }
        }
        return $courses;
    }

    /**
     * Collect user datafield informations
     *
     * @return array
     */
    public function getDatafields()
    {
        // generische Datenfelder aufsammeln
        $short_datafields = [];
        $long_datafields  = [];
        foreach (DataFieldEntry::getDataFieldEntries($this->current_user->user_id, 'user') as $entry) {
            if ($entry->isVisible() && $entry->getDisplayValue()
                && Visibility::verify($entry->getID(), $this->current_user->user_id))
            {
                if ($entry instanceof DataFieldTextareaEntry) {
                    $long_datafields[] = $entry;
                } else {
                    $short_datafields[] = $entry;
                }
            }
        }

        return [
            'long'  => $long_datafields,
            'short' => $short_datafields,
        ];
    }

    /**
     * Filter long datafiels from the datafields
     *
     * @return array
     */
    public function getLongDatafields()
    {
        $datafields = $this->getDatafields();
        $array      = [];

        if (empty($datafields)) {
            return null;
        }
        foreach ($datafields['long'] as $entry) {
            $array[(string) $entry->getName()] = [
                'content' => $entry->getDisplayValue(),
                'visible' => '(' . $entry->getPermsDescription() . ')',
            ];
        }

        return $array;
    }

    /**
     * Filter short datafiels from the datafields
     *
     * @return array
     */
    public function getShortDatafields()
    {
        $shortDatafields = $this->getDatafields();
        $array = [];

        if (empty($shortDatafields)) {
            return null;
        }

        foreach ($shortDatafields['short'] as $entry) {
            $array[(string) $entry->getName()] = [
                'content' => $entry->getDisplayValue(),
                'visible' => '(' . $entry->getPermsDescription() . ')',
            ];
        }
        return $array;
    }
}
