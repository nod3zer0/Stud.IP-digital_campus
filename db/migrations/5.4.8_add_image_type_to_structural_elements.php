<?php

class AddImageTypeToStructuralElements extends Migration
{
    public function description()
    {
        return 'Add field `image_type` to table `cw_structural_elements`';
    }

    public function up()
    {
        $db = DBManager::get();
        $db->exec(
            sprintf(
                'ALTER TABLE `cw_structural_elements` ' .
                    'ADD `image_type` ENUM("%s", "%s") NOT NULL DEFAULT "%1$s" AFTER `image_id`',
                \FileRef::class,
                \StockImage::class
            )
        );
    }

    public function down()
    {
        $db = DBManager::get();
        $db->exec('ALTER TABLE `cw_structural_elements` DROP `image_type`');
    }
}
