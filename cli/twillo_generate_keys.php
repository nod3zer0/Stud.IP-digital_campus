#!/usr/bin/env php
<?php
require_once 'studip_cli_env.inc.php';

$app_id = str_replace(" ", "-", Config::get()->UNI_NAME_CLEAN);

$privatekey = @file_get_contents('../config/twillo-private.key');

if (!$privatekey) {
    $key = EduSharingHelper::generateKeyPair();
    $success = file_put_contents(
        '../config/twillo.properties.xml',
        EduSharingHelper::generateEduAppXMLData($app_id, $key['publickey'])
    );
    if ($success !== false) {
        file_put_contents(
            '../config/twillo-private.key',
            $key['privatekey']
        );
        Config::get()->store('OERCAMPUS_TWILLO_APPID', $app_id);
        echo "Wrote config/twillo.properties.xml file. Next steps:

- Send the file config/twillo.properties.xml to the twillo.de administrators support.twillo@tib.eu . If they ask you to change the app-ID, you can do this in the system at Admin -> System -> Configuration by editing the config value OERCAMPUS_TWILLO_APPID.
- Leave the config/twillo-private.key file as it is in the config folder. This file is important for the API to work.
- You need to either have or create a datafield for users which contains the DFN-AAI-ID of a person. Without this ID in the datafield a user cannot upload to twillo.de due to technical restrictions.
- Copy the datafield_id of this datafield (you find this in the DB-table datafields). In Stud.IP go to Admin -> System -> Configuration and edit the config value OERCAMPUS_TWILLO_DFNAAIID_DATAFIELD. What you inserted, should look like 6a9ee3ca3685d2551698a3dc6f0f0eff.
- Once the twillo.de adminstrators received your file and registered your Stud.IP, you can enable the connection to twillo: Go to Admin -> System -> Configuration and edit the config value OERCAMPUS_ENABLE_TWILLO and switch it on.

There you go. From now on all your users with a given DFN-AAI-ID should be able to upload their OERs to twillo.de.
";
    } else {
        echo "No permission to write into folder config. Cannot save private and public keys.\n";
    }
} else {
    echo "Private key already exists. Remove it or be happy that it is there.\n";
}
