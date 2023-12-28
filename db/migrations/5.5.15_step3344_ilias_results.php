<?php
require_once 'lib/cronjobs/import_ilias_testresults.php';
final class Step3344IliasResults extends Migration
{

    use DatabaseMigrationTrait;

    public function description()
    {
        return 'adds column passed to table `grading_instances`, add cronjob';
    }

    protected function up()
    {
        if (!$this->columnExists('grading_instances', 'passed')) {
            DBManager::get()->exec("ALTER TABLE `grading_instances` ADD
                `passed` TINYINT NOT NULL DEFAULT 0 AFTER `feedback`");

        }
        ImportIliasTestresults::register()->schedulePeriodic(45, 1);
    }

    protected function down()
    {
        if ($this->columnExists('grading_instances', 'passed')) {
            DBManager::get()->exec("ALTER TABLE `grading_instances` DROP
                `passed`");
        }
        ImportIliasTestresults::unregister();
    }

}
