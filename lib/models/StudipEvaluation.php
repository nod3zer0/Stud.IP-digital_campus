<?php
/**
 * Evaluation.php
 * model class for table Evaluation
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Florian Bieringer <florian.bieringer@uni-passau.de>
 * @copyright   2014 Stud.IP Core-Group
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       3.0
 *
 * @property string $id alias column for eval_id
 * @property string $eval_id database column
 * @property string $author_id database column
 * @property string $title database column
 * @property string $text database column
 * @property int|null $startdate database column
 * @property int|null $stopdate database column
 * @property int|null $timespan database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property int $anonymous database column
 * @property int $visible database column
 * @property int $shared database column
 * @property User $author belongs_to User
 * @property SimpleORMapCollection|User[] $participants has_and_belongs_to_many User
 * @property mixed $enddate additional field
 */
class StudipEvaluation extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'eval';

        $config['belongs_to']['author'] = [
            'class_name' => User::class,
            'foreign_key' => 'author_id'
        ];
        $config['has_and_belongs_to_many']['participants'] = [
            'class_name' => User::class,
            'thru_table' => 'eval_user'
        ];

        $config['additional_fields']['enddate'] = true;

        parent::configure($config);
    }

    /**
     * Fetches all evaluations for a specific range_id
     *
     * @param String $range_id the range id
     * @return Array All evaluations for that range
     */
    public static function findByRange_id($range_id)
    {
        return self::findThru($range_id, [
            'thru_table'        => 'eval_range',
            'thru_key'          => 'range_id',
            'thru_assoc_key'    => 'eval_id',
            'assoc_foreign_key' => 'eval_id'
        ]);
    }

    /**
     * Returns the enddate of a evaluation. Returns null if stop is manual
     *
     * @return stopdate or null
     */
    public function getEnddate()
    {
        if ($this->stopdate) {
            return $this->stopdate;
        }
        if ($this->timespan) {
            return $this->startdate + $this->timespan;
        }
        return null;
    }

    function getNumberOfVotes ()
    {
        return DBManager::get()->fetchColumn("SELECT count(DISTINCT user_id) FROM eval_user WHERE eval_id = ?", [$this->id]);
    }
}
