<?php
final class RemoveFlashFromConfig extends Migration
{
    private const OLD_DESCRIPTION = 'Sollen externe Medien über [img/flash/audio/video] eingebunden werden? deny=nicht erlaubt, allow=erlaubt, proxy=proxy benutzen.';
    private const NEW_DESCRIPTION = 'Sollen externe Medien über [img/audio/video] eingebunden werden? deny=nicht erlaubt, allow=erlaubt, proxy=proxy benutzen.';


    public function description()
    {
        return 'Removes reference to flash from config description';
    }

    protected function up()
    {
        $query = "UPDATE `config` 
                  SET `description` = :new_description
                  WHERE `field` = 'LOAD_EXTERNAL_MEDIA'
                    AND `description` = :old_description";
        DBManager::get()->execute($query, [
            ':new_description' => self::NEW_DESCRIPTION,
            ':old_description' => self::OLD_DESCRIPTION,
        ]);
    }

    protected function down()
    {
        $query = "UPDATE `config` 
                  SET `description` = :old_description
                  WHERE `field` = 'LOAD_EXTERNAL_MEDIA'
                    AND `description` = :new_description";
        DBManager::get()->execute($query, [
            ':new_description' => self::NEW_DESCRIPTION,
            ':old_description' => self::OLD_DESCRIPTION,
        ]);
    }
}
