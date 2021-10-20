<?php
final class FixMyCoursesGroupConfig extends Migration
{
    public function description()
    {
        return 'Ensures correct format of MY_COURSES_OPEN_GROUPS user config option';
    }

    public function up()
    {
        ConfigValue::findEachBySQL(
            function ($value) {
                $groups = json_decode($value->value, true);

                $changed = false;
                foreach ($groups as $index => $val) {
                    if ($val === true) {
                        unset($groups[$index]);
                        $groups[] = $index;

                        $changed = true;
                    }
                }

                if ($changed) {
                    $value->value = json_encode(array_values($groups));
                    $value->store();
                }
            },
            'field = ?',
            ['MY_COURSES_OPEN_GROUPS']
        );
    }
}
