<?php

class AddMyCoursesWidget extends Migration
{
    public function description()
    {
        return 'add MyCoursesWidget (if not previously installed)';
    }

    public function up()
    {
        $db = DBManager::get();

        // check for previous installation
        $plugin_id = $db->fetchColumn('SELECT pluginid FROM plugins WHERE pluginclassname = ?', ['MyCoursesWidget']);

        if ($plugin_id) {
            $db->execute("UPDATE plugins SET pluginpath = '' WHERE pluginid = ?", [$plugin_id]);
        } else {
            // get position
            $pos = $db->fetchColumn("SELECT MAX(navigationpos) + 1 FROM plugins WHERE plugintype = 'PortalPlugin'");

            // install as portal plugin
            $sql = "INSERT INTO plugins (pluginclassname, pluginname, plugintype, enabled, navigationpos) VALUES (?)";
            $db->execute($sql, [['MyCoursesWidget', 'MyCoursesWidget', 'PortalPlugin', 'yes', $pos]]);

            $sql = "INSERT INTO roles_plugins (roleid, pluginid)
                    SELECT roleid, ? FROM roles WHERE `system` = 'y' AND rolename != 'Nobody'";
            $db->execute($sql, [$db->lastInsertId()]);
        }
    }

    public function down()
    {
        $db = DBManager::get();

        $plugin_id = $db->fetchColumn('SELECT pluginid FROM plugins WHERE pluginclassname = ?', ['MyCoursesWidget']);

        $db->execute('DELETE FROM widget_default WHERE pluginid = ?', [$plugin_id]);
        $db->execute('DELETE FROM widget_user WHERE pluginid = ?', [$plugin_id]);
        $db->execute('DELETE FROM roles_plugins WHERE pluginid = ?', [$plugin_id]);
        $db->execute('DELETE FROM plugins WHERE pluginid = ?', [$plugin_id]);
    }
}
