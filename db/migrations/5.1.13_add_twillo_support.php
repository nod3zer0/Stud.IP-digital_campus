<?php

class AddTwilloSupport extends Migration
{
    public function description()
    {
        return "Adds Twillo to OER Campus.";
    }

    public function up()
    {
        DBManager::get()->exec("
            ALTER TABLE `oer_material`
                ADD COLUMN `uri` varchar(1000) NOT NULL DEFAULT '' AFTER `license_identifier`,
                ADD COLUMN `uri_hash` char(32) NOT NULL DEFAULT '' AFTER `uri`,
                ADD COLUMN `published_id_on_twillo` varchar(50) DEFAULT NULL AFTER `uri_hash`,
                ADD KEY `uri_hash` (`uri_hash`)
        ");
        $statement = DBManager::get()->prepare("
            SELECT *
            FROM `oer_material`
            WHERE `host_id` IS NULL
        ");
        $statement->execute();
        $update_uri = DBManager::get()->prepare("
            UPDATE `oer_material`
            SET `uri` = :uri,
                `uri_hash` = :uri_hash
            WHERE `material_id` = :material_id
        ");
        while ($material_data = $statement->fetch()) {
            $uri = ($GLOBALS['OER_PREFERRED_URI'] ?: $GLOBALS['ABSOLUTE_URI_STUDIP'])
                . "dispatch.php/oer/market/details/"
                . $material_data['material_id'];
            $update_uri->execute([
                'uri' => $uri,
                'uri_hash' => md5($uri),
                'material_id' => $material_data['material_id']
            ]);
        }
        DBManager::get()->exec("
            ALTER TABLE `licenses`
                ADD COLUMN `twillo_licensekey` varchar(16) DEFAULT NULL AFTER `description`,
                ADD COLUMN `twillo_cclicenseversion` varchar(8) DEFAULT NULL AFTER `twillo_licensekey`
        ");
        DBManager::get()->exec("
            UPDATE `licenses`
            SET `twillo_licensekey` = 'CC_BY',
                `twillo_cclicenseversion` = '1.0'
            WHERE `identifier` = 'CC-BY-1.0'
        ");
        DBManager::get()->exec("
            UPDATE `licenses`
            SET `twillo_licensekey` = 'CC_BY',
                `twillo_cclicenseversion` = '2.0'
            WHERE `identifier` = 'CC-BY-2.0'
        ");
        DBManager::get()->exec("
            UPDATE `licenses`
            SET `twillo_licensekey` = 'CC_BY',
                `twillo_cclicenseversion` = '2.5'
            WHERE `identifier` = 'CC-BY-2.5'
        ");
        DBManager::get()->exec("
            UPDATE `licenses`
            SET `twillo_licensekey` = 'CC_BY',
                `twillo_cclicenseversion` = '3.0'
            WHERE `identifier` = 'CC-BY-3.0'
        ");
        DBManager::get()->exec("
            UPDATE `licenses`
            SET `twillo_licensekey` = 'CC_BY',
                `twillo_cclicenseversion` = '4.0'
            WHERE `identifier` = 'CC-BY-4.0'
        ");
        DBManager::get()->exec("
            UPDATE `licenses`
            SET `twillo_licensekey` = 'CC_BY_SA',
                `twillo_cclicenseversion` = '1.0'
            WHERE `identifier` = 'CC-BY-SA-1.0'
        ");
        DBManager::get()->exec("
            UPDATE `licenses`
            SET `twillo_licensekey` = 'CC_BY_SA',
                `twillo_cclicenseversion` = '2.0'
            WHERE `identifier` = 'CC-BY-SA-2.0'
        ");
        DBManager::get()->exec("
            UPDATE `licenses`
            SET `twillo_licensekey` = 'CC_BY_SA',
                `twillo_cclicenseversion` = '2.5'
            WHERE `identifier` = 'CC-BY-SA-2.5'
        ");
        DBManager::get()->exec("
            UPDATE `licenses`
            SET `twillo_licensekey` = 'CC_BY_SA',
                `twillo_cclicenseversion` = '3.0'
            WHERE `identifier` = 'CC-BY-SA-3.0'
        ");
        DBManager::get()->exec("
            UPDATE `licenses`
            SET `twillo_licensekey` = 'CC_BY_SA',
                `twillo_cclicenseversion` = '4.0'
            WHERE `identifier` = 'CC-BY-SA-4.0'
        ");
        DBManager::get()->exec("
            UPDATE `licenses`
            SET `twillo_licensekey` = 'CC_0',
                `twillo_cclicenseversion` = '1.0'
            WHERE `identifier` = 'CC0-1.0'
        ");

        DBManager::get()->exec(
            "INSERT IGNORE INTO `config`
            (`field`, `value`, `type`, `range`,
            `section`,
            `mkdate`, `chdate`,
            `description`)
            VALUES
            ('OERCAMPUS_ENABLE_TWILLO', '0', 'boolean', 'global',
            'OERCampus', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
            'Soll der Upload zu twillo.de vom OERCampus möglich sein? Folgen Sie dazu der Installationsanleitung.')"
        );
        DBManager::get()->exec(
            "INSERT IGNORE INTO `config`
            (`field`, `value`, `type`, `range`,
            `section`,
            `mkdate`, `chdate`,
            `description`)
            VALUES
            ('OERCAMPUS_TWILLO_APPID', '', 'string', 'global',
            'OERCampus', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
            'Welche ID hat dieses Stud.IP, wenn es mit twillo.de kommuniziert?')"
        );
        DBManager::get()->exec(
            "INSERT IGNORE INTO `config`
            (`field`, `value`, `type`, `range`,
            `section`,
            `mkdate`, `chdate`,
            `description`)
            VALUES
            ('OERCAMPUS_TWILLO_DFNAAIID_DATAFIELD', '', 'string', 'global',
            'OERCampus', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(),
            'Welches Datenfeld eines Nutzers trägt dessen DFN-AAI-ID?')"
        );

        SimpleORMap::expireTableScheme();
    }

    public function down()
    {
        DBManager::get()->exec("
            ALTER TABLE `licenses`
                DROP COLUMN `twillo_licensekey`,
                DROP COLUMN `twillo_cclicenseversion`
        ");
        DBManager::get()->exec("
            ALTER TABLE `oer_material`
                DROP COLUMN `uri`,
                DROP COLUMN `uri_hash`
        ");
    }
}

