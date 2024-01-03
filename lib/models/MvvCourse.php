<?php
/**
 * MvvCourse.php
 * Model class for courses in context of MVV
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Peter Thienel <thienel@data-quest.de>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.5
 *
 * @property string $id alias column for seminar_id
 * @property string $seminar_id database column
 * @property string|null $veranstaltungsnummer database column
 * @property string $institut_id database column
 * @property string $name database column
 * @property string|null $untertitel database column
 * @property int $status database column
 * @property string $beschreibung database column
 * @property string|null $ort database column
 * @property string|null $sonstiges database column
 * @property int $lesezugriff database column
 * @property int $schreibzugriff database column
 * @property int|null $start_time database column
 * @property int|null $duration_time database column
 * @property string|null $art database column
 * @property string|null $teilnehmer database column
 * @property string|null $vorrausetzungen database column
 * @property string|null $lernorga database column
 * @property string|null $leistungsnachweis database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property string|null $ects database column
 * @property int|null $admission_turnout database column
 * @property int|null $admission_binding database column
 * @property int $admission_prelim database column
 * @property string|null $admission_prelim_txt database column
 * @property int $admission_disable_waitlist database column
 * @property int $visible database column
 * @property int|null $showscore database column
 * @property string|null $aux_lock_rule database column
 * @property int $aux_lock_rule_forced database column
 * @property string|null $lock_rule database column
 * @property int $admission_waitlist_max database column
 * @property int $admission_disable_waitlist_move database column
 * @property int $completion database column
 * @property string|null $parent_course database column
 */

class MvvCourse extends ModuleManagementModelTreeItem
{

    protected static function configure($config = [])
    {
        $config['db_table'] = 'seminare';

        parent::configure($config);
    }

    /**
     * @see MvvTreeItem::getTrailParentId()
     */
    public function getTrailParentId()
    {
        return ($_SESSION['MVV/MvvCourse/trail_parent_id']);
    }

    /**
     * @see MvvTreeItem::getTrailParent()
     */
    public function getTrailParent()
    {
        return Lvgruppe::findCached($this->getTrailParentId());
    }

    /**
     * @see MvvTreeItem::getChildren()
     */
    public function getChildren()
    {
        return null;
    }

    /**
     * @see MvvTreeItem::hasChildren()
     */
    public function hasChildren()
    {
        return false;
    }

    public function getDisplayName()
    {
        return $this->name;
    }

    /**
     * @see MvvTreeItem::getParents()
     */
    public function getParents($mode = null)
    {
       return Lvgruppe::findBySeminar($this->getId());
    }
}
