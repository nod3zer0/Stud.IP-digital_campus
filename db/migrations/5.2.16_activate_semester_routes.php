<?php
class ActivateSemesterRoutes extends Migration
{
    public function description()
    {
        return "Activates all semester routes";
    }

    public function up()
    {
        require_once 'app/routes/Semester.php';
        RESTAPI\ConsumerPermissions::get()->activateRouteMap(new RESTAPI\Routes\Semester());
    }
}
