<?php
/**
 * @author      Jan-Hendrik Willms <tleilx+studip@gail.com>
 * @license     GPL2 or any later version
 * @category    Stud.IP
 */
class RangeSearch extends SearchType
{
    /**
     * returns the title/description of the searchfield
     *
     * @return string title/description
     */
    public function getTitle()
    {
        return _('Person, Veranstaltung oder Einrichtung suchen');
    }

    /**
     * returns a sql-string appropriate for the searchtype of the current class
     *
     * @return string
     */
    private function getSQL()
    {
        $this->extendedLayout = true;

        $queries = [];
        $queries[] = "SELECT user_id AS id,
                             TRIM(CONCAT(Nachname, ', ', Vorname, ' (', username, ')')) AS name,
                             'user' AS type
                      FROM auth_user_md5
                      LEFT JOIN user_info USING (user_id)
                      WHERE (
                          CONCAT(Nachname, ', ', Vorname, ' ', Nachname) LIKE REPLACE(:input, ' ', '% ')
                          OR username LIKE :input
                        )
                        AND " . get_vis_query();
        $queries[] = "SELECT Seminar_id AS id,
                             TRIM(CONCAT(VeranstaltungsNummer, ' ', Name)) AS name,
                             'course' AS type
                      FROM seminare
                      WHERE CONCAT(VeranstaltungsNummer, ' ', Name, ' ', Untertitel) LIKE REPLACE(:input, ' ', '% ')";
        $queries[] = "SELECT Institut_id AS id,
                             Name AS name,
                             'institute' AS type
                      FROM Institute
                      WHERE Name LIKE REPLACE(:input, ' ', '% ')";
        $queries = implode(" UNION ALL ", $queries);

        return "SELECT *
                FROM ({$queries}) AS tmp
                ORDER BY name ASC";
    }

    /**
     * {@inheritdoc}
     */
    public function getResults($input, $contextual_data = [], $limit = PHP_INT_MAX, $offset = 0)
    {
        $query = $this->getSQL();

        if ($offset || $limit != PHP_INT_MAX) {
            $query .= sprintf(' LIMIT %u, %u', $offset, $limit);
        }

        return DBManager::get()->fetchAll($query, [':input' => "%{$input}%"], function ($row) {
            $range = RangeFactory::createRange($row['type'], null);
            return [
                $row['id'],
                $range->describeRange() . ': ' . $row['name'],
            ];
        });

    }

    /**
     * A very simple overwrite of the same method from SearchType class.
     * returns the absolute path to this class for autoincluding this class.
     *
     * @return: path to this class
     */
    public function includePath()
    {
        return studip_relative_path(__FILE__);
    }
}
