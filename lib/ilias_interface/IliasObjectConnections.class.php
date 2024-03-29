<?php
# Lifter003: TEST
# Lifter007: TODO
# Lifter010: TODO
/**
* class to handle object connections
*
* This class contains methods to handle connections between stud.ip-objects and external content.
*
* @author   Arne Schröder <schroeder@data-quest.de>
* @access   public
* @modulegroup  ilias_interface_modules
* @module       IliasObjectConnections
* @package  Ilias-Interface
*/
class IliasObjectConnections
{
    public $id;
    public $object_connections;
    /**
    * constructor
    *
    * init class.
    * @access public
    * @param string $object_id object-id
    */
    function __construct($object_id = "")
    {
        $this->id = $object_id;
        if ($object_id != "")
            $this->readData();
    }

    /**
    * read object connections
    *
    * gets object connections from database
    * @access public
    */
    function readData()
    {
        $this->object_connections = [];

        $query = "SELECT system_type, module_type, module_id, chdate
                  FROM object_contentmodules
                  WHERE object_id = ?
                  ORDER BY chdate DESC";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->id]);

        $module_count = 0;
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $module_count += 1;
            $d_system_type = $row['system_type'];
            $d_module_type = $row['module_type'];
            $d_module_id   = $row['module_id'];

            $reference = $d_system_type . '_' . $d_module_type . '_' . $d_module_id;
            $reference = $d_module_id;
            $this->object_connections[$d_system_type][$reference]['index']    = $d_system_type;
            $this->object_connections[$d_system_type][$reference]['type']   = $d_module_type;
            $this->object_connections[$d_system_type][$reference]['id']     = $d_module_id;
            $this->object_connections[$d_system_type][$reference]['chdate'] = $row['chdate'];
        }

        if ($module_count == 0) {
            $this->object_connections = false;
        }
    }

    /**
    * get object connections
    *
    * returns object connections
    * @access public
    * @return array object connections
    */
    function getConnections()
    {
        return $this->object_connections;
    }

    /**
    * get connection-status
    *
    * returns true, if object has connections
    * @access public
    * @return boolean connection-status
    */
    function isConnected($ilias_index = '')
    {
        if ($ilias_index) {
            return (boolean) $this->object_connections[$ilias_index];
        } else {
            return (boolean) $this->object_connections;
        }
    }

    /**
     * get connection-status
     *
     * returns true, if object has connections
     * @access public
     * @param string $object_id object-id (optional)
     * @return boolean connection-status
     */
    public static function isObjectConnected($index, $object_id)
    {
        if ($object_id && $index) {
            $query = "SELECT 1 FROM object_contentmodules WHERE object_id = ? AND system_type = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$object_id, $index]);
            return (bool) $statement->fetchColumn();
        }

        return false;
    }

    /**
     * get connection-status
     *
     * returns true, if course has connections to ILIAS courses
     * @access public
     * @param string $object_id object-id (optional)
     * @return boolean connection-status
     */
    public static function isCourseConnected($object_id)
    {
        if ($object_id) {
            $query = "SELECT 1 FROM object_contentmodules WHERE object_id = ? AND module_type = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([$object_id, 'crs']);
            return (bool)$statement->fetchColumn();
        } else {
            return false;
        }
    }

    /**
    * get module-id
    *
    * returns module-id of given connection
    * @access public
    * @param string $connection_object_id object-id
    * @param string $connection_module_type module-type
    * @param string $connection_cms system-type
    * @return string module-id
    */
    public static function getConnectionModuleId($connection_object_id, $connection_module_type, $connection_cms)
    {
        $query = "SELECT module_id
                  FROM object_contentmodules
                  WHERE object_id = ? AND system_type = ? AND module_type = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $connection_object_id,
            $connection_cms,
            $connection_module_type
        ]);
        return $statement->fetchColumn() ?: false;
    }

    /**
    * set connection
    *
    * sets connection with object
    * @access public
    * @param string $connection_object_id object-id
    * @param string $connection_module_id module-id
    * @param string $connection_module_type module-type
    * @param string $connection_cms system-type
    * @return boolean successful
    */
    public static function setConnection($connection_object_id, $connection_module_id, $connection_module_type, $connection_cms)
    {
        $query = "SELECT 1
                  FROM object_contentmodules
                  WHERE object_id = ? AND module_id = ? AND system_type = ?
                    AND module_type = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $connection_object_id,
            $connection_module_id,
            $connection_cms,
            $connection_module_type
        ]);
        $check = $statement->fetchColumn();

        if ($check) {
            $query = "UPDATE object_contentmodules
                      SET module_type = ?, chdate = UNIX_TIMESTAMP()
                      WHERE object_id = ? AND module_id = ? AND system_type = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $connection_module_type,
                $connection_object_id,
                $connection_module_id,
                $connection_cms
            ]);
        } else {
            $query = "INSERT INTO object_contentmodules
                        (object_id, module_id, system_type, module_type, mkdate, chdate)
                      VALUES (?, ?, ?, ?, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $connection_object_id,
                $connection_module_id,
                $connection_cms,
                $connection_module_type
            ]);
        }
        return true;
    }

    /**
    * unset connection
    *
    * deletes connection with object
    * @access public
    * @param string $connection_object_id object-id
    * @param string $connection_module_id module-id
    * @param string $connection_module_type module-type
    * @param string $connection_cms system-type
    * @return boolean successful
    */
    public static function unsetConnection($connection_object_id, $connection_module_id, $connection_module_type, $connection_cms)
    {
        $query = "SELECT 1
                  FROM object_contentmodules
                  WHERE object_id = ? AND module_id = ? AND system_type = ?
                    AND module_type = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([
            $connection_object_id,
            $connection_module_id,
            $connection_cms,
            $connection_module_type
        ]);
        $check = $statement->fetchColumn();


        if ($check) {
            $query = "DELETE FROM object_contentmodules
                      WHERE object_id = ? AND module_id = ? AND system_type = ?
                        AND module_type = ?";
            $statement = DBManager::get()->prepare($query);
            $statement->execute([
                $connection_object_id,
                $connection_module_id,
                $connection_cms,
                $connection_module_type
            ]);
            return true;
        }
        return false;
    }

    public static function GetConnectedSystems($object_id)
    {
        $query = "SELECT DISTINCT system_type
                  FROM object_contentmodules
                  WHERE object_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$object_id]);
        return $statement->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function DeleteAllConnections($object_id, $cms_type)
    {
        $query = "DELETE FROM object_contentmodules
                  WHERE object_id = ? AND system_type = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$object_id, $cms_type]);
        return $statement->rowCount();
    }

    /**
     * @param Course $course
     * @return int
     */
    public static function importIliasResultsForCourse(Course $course): int
    {
        $connected_ilias = [];
        $students = new SimpleCollection($course->getMembersWithStatus('autor'));
        $num = 0;
        foreach (Grading\Definition::findBySQL("course_id = ? AND tool='ILIAS'", [$course->id]) as $definition) {
            [$index, $module_id, $import_type] = explode('-', $definition->item);
            if (!isset($connected_ilias[$index])) {
                $connected_ilias[$index] = new ConnectedIlias($index);
            }
            $test_result = $connected_ilias[$index]->soap_client->getTestResults($module_id);
            foreach ($test_result as $result) {
                $ilias_user = $connected_ilias[$index]->getConnectedUser($result['user_id']);
                if ($ilias_user) {
                    $member = $students->findOneBy('user_id', $ilias_user->getStudipId());
                    if ($member) {
                        $grade = Grading\Instance::import([
                                'definition_id' => $definition->id,
                                'user_id'       => $member->user_id,
                                'rawgrade'      => $import_type & 1 && $result['maximum_points'] ? $result['received_points'] / $result['maximum_points'] : 0,
                                'passed'        => $import_type & 2 ? $result['passed'] : 0
                            ]
                        );
                        $num += $grade->store();
                    }
                }
            }
        }
        return $num;
    }
}
