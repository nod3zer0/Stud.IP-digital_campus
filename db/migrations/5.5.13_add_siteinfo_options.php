<?php
final class AddSiteinfoOptions extends Migration
{
    public function description()
    {
        return 'adds options for siteinfo pages';
    }

    public function up()
    {
        $db = DBManager::get();
        $db->exec("
            ALTER TABLE `siteinfo_details`
                ADD `page_disabled_nobody` TINYINT NOT NULL DEFAULT '0' AFTER `draft_status`
        ");
        $position = 1;
        foreach($db->fetchFirst("SELECT rubric_id
                                FROM siteinfo_rubrics
                                ORDER BY position, rubric_id ASC") as $rubric_id) {
            $db->execute("UPDATE siteinfo_rubrics SET position=? WHERE rubric_id=?", [$position++, $rubric_id]);
            $page_position = 1;
            foreach($db->fetchFirst("SELECT detail_id
                                FROM siteinfo_details
                                WHERE rubric_id = ?
                                ORDER BY position, detail_id ASC", [$rubric_id]) as $detail_id) {
                $db->execute("UPDATE siteinfo_details SET position=? WHERE detail_id=?", [$page_position++, $detail_id]);
            }
        }
    }

    public function down()
    {
        DBManager::get()->exec("
            ALTER TABLE `siteinfo_details`
                DROP `page_disabled_nobody`
        ");
    }
}
