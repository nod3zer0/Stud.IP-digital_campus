<?php
/**
 * Kategorie model
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * @author      Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GPL version 2
 * @category    Stud.IP
 * @since       2.4
 *
 * @property string $id alias column for kategorie_id
 * @property string $kategorie_id database column
 * @property string $range_id database column
 * @property I18NString $name database column
 * @property I18NString $content database column
 * @property int $mkdate database column
 * @property int $chdate database column
 * @property int $priority database column
 * @property User $user belongs_to User
 */

class Kategorie extends SimpleORMap
{
    /**
     * Configures the model.
     *
     * @param array $config Configuration array
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'kategorien';

        $config['belongs_to'] = [
            'user' => [
                'class_name'  => User::class,
                'foreign_key' => 'range_id',
            ],
        ];

        $config['i18n_fields'] = [
            'name' => true,
            'content' => true,
        ];

        parent::configure($config);
    }

    /**
     * Finds all categories of a specific user
     *
     * @param string $user_id Id of the user
     * @return Kategorie[] of category objects
     */
    public static function findByUserId(string $user_id): array
    {
        return self::findByRange_id($user_id, 'ORDER BY priority');
    }

    /**
     * Increases all category priorities of a user
     *
     * @param string $user_id Id of the user
     * @return bool indicating if anything has changed
     */
    public static function increasePrioritiesByUserId(string $user_id): bool
    {
        $query = "UPDATE kategorien SET priority = priority + 1 WHERE range_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user_id]);
        return $statement->rowCount() > 0;
    }
}
