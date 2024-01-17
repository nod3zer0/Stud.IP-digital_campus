<?
# Lifter002: TODO
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO
/*

archiv.inc.php - Funktionen zur Archivierung in Stud.IP
Copyright (C) 2000 Cornelis Kater <ckater@gwdg.de>, Ralf Stockmann <rstockm@gwdg.de>, Stefan Suchi <suchi@gmx.de>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

/**
 * This function returns the last activity in the course.
 *
 * @param  string $sem_id the id of the course
 * @return int timestamp of last activity (max chdate)
 */
function lastActivity ($sem_id)
{
    // Cache query generation
    static $query = null;
    if ($query === null) {
        $queries = [
            // Veranstaltungs-data
            "SELECT chdate FROM seminare WHERE Seminar_id = :id",
            // Folders
            "SELECT MAX(chdate) AS chdate FROM folders WHERE range_id = :id",
            // Documents
            "SELECT MAX(file_refs.chdate) AS chdate FROM file_refs
                INNER JOIN folders
                ON file_refs.folder_id = folders.id
                WHERE folders.range_id = :id",
            // SCM
            "SELECT MAX(chdate) AS chdate FROM scm WHERE range_id = :id",
            // Dates
            "SELECT MAX(chdate) AS chdate FROM termine WHERE range_id = :id",
            // News
            "SELECT MAX(`date`) AS chdate FROM news_range LEFT JOIN news USING (news_id) WHERE range_id = :id",
        ];

        // Votes
        if (Config::get()->VOTE_ENABLE) {
            $queries[] = "SELECT MAX(questionnaires.chdate) AS chdate FROM questionnaires INNER JOIN questionnaire_assignments ON (questionnaire_assignments.questionnaire_id = questionnaires.questionnaire_id) WHERE questionnaire_assignments.range_id = :id";
        }

        // Wiki
        if (Config::get()->WIKI_ENABLE) {
            $queries[] = "SELECT MAX(`chdate`) AS chdate
                          FROM `wiki_pages`
                          WHERE `range_id` = :id";
        }

        foreach (PluginEngine::getPlugins('ForumModule') as $plugin) {
            $table = $plugin->getEntryTableInfo();
            $queries[] = 'SELECT MAX(`'. $table['chdate'] .'`) AS chdate FROM `'. $table['table'] .'` WHERE `'. $table['seminar_id'] .'` = :id';
        }

        $query = "SELECT MAX(chdate) FROM (" . implode(' UNION ', $queries) . ") AS tmp";
    }

    $statement = DBManager::get()->prepare($query);
    $statement->bindValue(':id', $sem_id);
    $statement->execute();
    $timestamp = $statement->fetchColumn() ?: 0;

    //correct the timestamp, if date in the future (news can be in the future!)
    if ($timestamp > time()) {
        $timestamp = time();
    }

    return $timestamp;
}
