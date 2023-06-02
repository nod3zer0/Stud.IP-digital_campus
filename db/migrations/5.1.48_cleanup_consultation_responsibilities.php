<?php
final class CleanupConsultationResponsibilities extends Migration
{
    public function description()
    {
        return 'Remove all orphaned entries in table "consultation_responsibilities"';
    }

    protected function up()
    {
        $query = "DELETE FROM `consultation_responsibilities`
                  WHERE (
                    `range_type` = 'user'
                    AND `range_id` NOT IN (
                        SELECT `user_id`
                        FROM `auth_user_md5`
                    )
                  ) OR (
                    `range_type` = 'institute'
                    AND `range_id` NOT IN (
                        SELECT `Institut_id`
                        FROM `Institute`
                    )
                  ) OR (
                    `range_type` = 'statusgroup'
                    AND `range_id` NOT IN (
                        SELECT `statusgruppe_id`
                        FROM `statusgruppen`
                    )
                  )";
        DBManager::get()->exec($query);
    }
}
