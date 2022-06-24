<?php
final class AddCoursewarePublicLinks extends Migration
{
    public function description()
    {
        return 'Create Courseware public links database table';
    }

    public function up()
    {
        \DBManager::get()->exec("CREATE TABLE `cw_public_links` (
            `id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `user_id` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
            `structural_element_id` int(11) NOT NULL,
            `password` varbinary(64) NOT NULL,
            `expire_date` int(11) NOT NULL,
            `mkdate` int(11) NOT NULL,
            `chdate` int(11) NOT NULL,

            PRIMARY KEY (`id`),
            INDEX index_user_id (`user_id`),
            INDEX index_structural_element_id (`structural_element_id`)
            )
        ");
    }

    public function down()
    {
        \DBManager::get()->exec("DROP TABLE IF EXISTS `cw_public_links`");
    }
}
