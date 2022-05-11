<?php

class UpdateCorePlugins extends Migration
{
    private static $core_widgets = [
        'core/ActivityFeed' => 'ActivityFeed',
        'core/Blubber' => 'Blubber',
        'core/ContentsWidget' => 'ContentsWidget',
        'core/Forum' => 'CoreForum',
        'core/EvaluationsWidget' => 'EvaluationsWidget',
        'core/NewsWidget' => 'NewsWidget',
        'core/QuickSelection' => 'QuickSelection',
        'core/ScheduleWidget' => 'ScheduleWidget',
        'core/TerminWidget' => 'TerminWidget'
    ];

    public function description()
    {
        return 'convert old core plugins into new core pugins';
    }

    public function up()
    {
        $db = DBManager::get();
        $stmt = $db->prepare('UPDATE plugins SET pluginpath = ? WHERE pluginclassname = ?');

        foreach (self::$core_widgets as $core_widget) {
            $stmt->execute(['', $core_widget]);
        }

        $db->exec("UPDATE help_content SET route = REPLACE(route, 'plugins.php/coreforum', 'dispatch.php/course/forum')");
        $db->exec("UPDATE help_tour_steps SET route = REPLACE(route, 'plugins.php/coreforum', 'dispatch.php/course/forum')");
    }

    public function down()
    {
        $db = DBManager::get();

        $stmt = $db->prepare('UPDATE plugins SET pluginpath = ? WHERE pluginclassname = ?');

        foreach (self::$core_widgets as $pluginpath => $core_widget) {
            $stmt->execute([$pluginpath, $core_widget]);
        }

        $db->exec("UPDATE help_content SET route = REPLACE(route, 'dispatch.php/course/forum', 'plugins.php/coreforum')");
        $db->exec("UPDATE help_tour_steps SET route = REPLACE(route, 'dispatch.php/course/forum', 'plugins.php/coreforum')");
    }
}
