<?php

/**
 * Class TwilloConnector manages all transactions between Stud.IP and twillo.de.
 */
class TwilloConnector
{
    /**
     * Caches the edusharing EDU-ticket for the same request.
     * @var null|string
     */
    static protected $ticket = null;

    static public $twillo_base_url = 'https://www.twillo.de/edu-sharing';

    public static function getHttpProxy()
    {
        $stream_context_options = stream_context_get_options(get_default_http_stream_context(self::$twillo_base_url));
        return isset($stream_context_options['http']['proxy']) ? Config::get()->HTTP_PROXY : '';
    }
    /**
     * Returns the DFN-AAI-ID for the given user. This ID must be the content of a datafield with the
     * datafield_id in the global config OERCAMPUS_TWILLO_DFNAAIID_DATAFIELD. If either this config or
     * the content of the datafield is null, this method returns false;
     * @param null $user_id
     * @return false|string
     */
    public static function getTwilloUserID($user_id = null)
    {
        $user_id || $user_id = User::findCurrent()->id;
        if (Config::get()->OERCAMPUS_TWILLO_DFNAAIID_DATAFIELD) {
            $entry = DatafieldEntryModel::findOneBySQL('`datafield_id` = :datafield_id AND `range_id` = :user_id ', [
                'datafield_id' => Config::get()->OERCAMPUS_TWILLO_DFNAAIID_DATAFIELD,
                'user_id' => $user_id
            ]);
            if ($entry) {
                return $entry['content'] ?: false;
            }
        }
        return false;
    }

    /**
     * Transfers the material to twillo.
     * @param OERMaterial $material : the material to transfer
     * @param null|string $user_id : The user in whose filesystem of twillo the material should be uploaded to.
     * @return bool|string : true on success, on failure a text-string as error-message
     * @throws Exception
     */
    public static function uploadMaterial(OERMaterial $material, $user_id = null)
    {
        $user_id || $user_id = User::findCurrent()->id;
        $base = new EduSharingHelperBase(
            self::$twillo_base_url,
            file_get_contents($GLOBALS['STUDIP_BASE_PATH']."/config/twillo-private.key"),
            Config::get()->OERCAMPUS_TWILLO_APPID,
            self::getHttpProxy()
        // 'data-quest-Test'
        );
        $authHelper = new EduSharingAuthHelper($base);
        if (!static::$ticket) {
            static::$ticket = $authHelper->getTicketForUser(TwilloConnector::getTwilloUserID($user_id));
        }

        //the use this edu-ticket to authenticate.

        if (!$material['published_id_on_twillo']) { //Anlegen des materials als Dateihülle:
            //frage, ob es einen Ordner gibt:
            $header = [];
            $header[] = "Authorization: EDU-TICKET ".static::$ticket;
            $header[] = "Content-Type: application/json";
            $header[] = "Accept: application/json";

            $cr = curl_init();
            curl_setopt($cr, CURLOPT_URL, self::$twillo_base_url . '/rest/node/v1/nodes/-home-/-userhome-/children');
            curl_setopt($cr, CURLOPT_HTTPHEADER, $header);
            curl_setopt($cr, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($cr, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($cr, CURLOPT_PROXY, self::getHttpProxy());
            $body = curl_exec($cr);
            $error = curl_error($cr);
            if ($error) {
                return $error;
            }
            curl_close($cr);
            $body = json_decode($body, true);


            $subfolder_id = null;

            foreach ($body['nodes'] as $nodedata) {
                if ($nodedata['name'] === "OERCampusPublications") {
                    $subfolder_id = $nodedata['ref']['id'];
                    break;
                }
            }

            if (!$subfolder_id) {
                //erstelle den Ordner, wenn es ihn nicht gibt:
                $header = [];
                $header[] = "Authorization: EDU-TICKET ".static::$ticket;
                $header[] = "Content-Type: application/json";
                $header[] = "Accept: application/json";

                $cr = curl_init();
                curl_setopt($cr, CURLOPT_POST, 1);
                curl_setopt($cr, CURLOPT_URL, self::$twillo_base_url . '/rest/node/v1/nodes/-home-/-userhome-/children?type=cm%3Afolder&renameIfExists=false&assocType=&versionComment=');
                curl_setopt($cr, CURLOPT_HTTPHEADER, $header);
                curl_setopt($cr, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($cr, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($cr, CURLOPT_PROXY, self::getHttpProxy());

                $postbody = json_encode([
                    'cm:edu_forcemetadataset' => ['true'],
                    'cm:edu_metadataset' => ['mds'],
                    'cm:name' => ['OERCampusPublications']
                ]);
                curl_setopt($cr, CURLOPT_POSTFIELDS, $postbody);
                $body = curl_exec($cr);
                curl_close($cr);
                $body = json_decode($body, true);

                $subfolder_id = $body['node']['ref']['id'];
            }

            //Erstelle die Datei als Link:
            $header = [];
            $header[] = "Authorization: EDU-TICKET ".static::$ticket;
            $header[] = "Content-Type: application/json";
            $header[] = "Accept: application/json";

            $cr = curl_init();
            curl_setopt($cr, CURLOPT_POST, 1);
            curl_setopt($cr, CURLOPT_URL, self::$twillo_base_url . '/rest/node/v1/nodes/-home-/'.$subfolder_id.'/children?type=ccm%3Aio&renameIfExists=true');
            curl_setopt($cr, CURLOPT_HTTPHEADER, $header);
            curl_setopt($cr, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($cr, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($cr, CURLOPT_PROXY, self::getHttpProxy());
            $postbody = json_encode([
                'ccm:wwwurl' => [$material->getDownloadUrl()],
                'ccm:linktype' => ["USER_GENERATED"],
                //'cm:name' => [$material['name']]
            ]);
            curl_setopt($cr, CURLOPT_POSTFIELDS, $postbody);
            $body = curl_exec($cr);
            curl_close($cr);

            $body = json_decode($body, true);
            $material['published_id_on_twillo'] = $body['node']['ref']['id'];
            $material->store();
        }


        $header = [];
        $header[] = "Authorization: EDU-TICKET ".static::$ticket;
        $header[] = "Content-Type: application/json";
        $header[] = "Accept: application/json";

        $cr = curl_init();
        curl_setopt($cr, CURLOPT_POST, 1);
        curl_setopt($cr, CURLOPT_URL, self::$twillo_base_url . '/rest/node/v1/nodes/-home-/'.$material['published_id_on_twillo'].'/metadata?versionComment=METADATA_UPDATE');
        curl_setopt($cr, CURLOPT_HTTPHEADER, $header);
        curl_setopt($cr, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($cr, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($cr, CURLOPT_PROXY, self::getHttpProxy());
        $old_base = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
        $permalink = URLHelper::getLink("dispatch.php/oer/market/details/".$material->getId());
        URLHelper::setBaseURL($old_base);
        $topics = array_map(function ($t) { return $t['name'];}, $material->getTopics());
        $postbody = json_encode([
            'ccm:original' => [""],
            'cm:created' => [(string) $material['mkdate']],
            'virtual:commentcount' => ["0"],
            'ccm:metadatacontributer_creatorVCARD_ORG' => [""],
            'cclom:version' => ["1.0"],
            'virtual:usagecount' => ["0"],
            'sys:node-uuid' => [$material['published_id_on_twillo']],
            'virtual:childobjectcount' => ["0"],
            'cclom:title' => [$material['name']],
            'ccm:linktype' => ['USER_GENERATED'],
            'ccm:lifecyclecontributer_authorVCARD_SURNAME' => ["Nachname"],
            'ccm:lifecyclecontributer_authorVCARD_URL' => [""],
            'ccm:lifecyclecontributer_authorVCARD_COUNTRY' => [''],
            'ccm:lifecyclecontributer_author' => ['BEGIN:VCARD\nN:Nachname;Vorname\nFN:Vorname Nachname\nVERSION:3.0\nEND:VCARD'],
            'ccm:lifecyclecontributer_authorVCARD_REGION' => [''],
            'ccm:lifecyclecontributer_authorVCARD_ORG' => [""],
            'ccm:lifecyclecontributer_authorVCARD_TITLE' => [""],
            'ccm:lifecyclecontributer_authorVCARD_STREET' => [''],
            'ccm:lifecyclecontributer_authorVCARD_PLZ' => [''],
            'ccm:lifecyclecontributer_authorVCARD_GIVENNAME' => ['Vorname'],
            'ccm:lifecyclecontributer_authorVCARD_CITY' => [''],
            'ccm:lifecyclecontributer_authorFN' => ["Vorname Nachname"],
            'ccm:lifecyclecontributer_authorVCARD_EMAIL' => [""],
            'ccm:lifecyclecontributer_authorVCARD_TEL' => [''],
            'ccm:metadatacontributer_creatorVCARD_CITY' => [''],
            'ccm:metadatacontributer_creatorVCARD_URL' => [''],
            'ccm:ccm:metadatacontributer_creatorVCARD_REGION' => [''],
            'ccm:metadatacontributer_creatorVCARD_PLZ' => [''],
            'ccm:metadatacontributer_creator' => ['BEGIN:VCARD\nVERSION:3.0\nN:OER-Campus, Stud.IP\nFN:Stud.IP\nORG:\nURL:\nTITLE:\nTEL;TYPE=WORK,VOICE:\nADR;TYPE=intl,postal,parcel,work:;;;;;;\nEMAIL;TYPE=PREF,INTERNET:\nEND:VCARD'],
            'ccm:metadatacontributer_creatorVCARD_TEL' => [''],
            'ccm:metadatacontributer_creatorVCARD_COUNTRY' => [''],
            'ccm:metadatacontributer_creatorVCARD_EMAIL' => [''],
            'ccm:metadatacontributer_creatorVCARD_TITLE' => [''],
            'ccm:metadatacontributer_creatorVCARD_GIVENNAME' => ['open'],
            'ccm:metadatacontributer_creatorVCARD_STREET' => [''],
            'ccm:metadatacontributer_creatorVCARD_SURNAME' => ["Stud.IP"],
            'ccm:metadatacontributer_creatorFN' => ["open cast"],
            'sys:store-protocol' => ['workspace'],
            'sys:store-identifier' => ['SpacesStore'],
            'ccm:version_comment' => ['METADATA_UPDATE'],
            'ccm:educationallearningresourcetype' => ['exercise'], //?
            'ccm:create_version' => ['true'],
            'cm:modifiedISO8601' => [date("/r", $material['chdate'])],
            'ccm:author_freetext' => [''],
            'sys:node-dbid' => ['836'],
            'ccm:wwwurl' => [$material->getDownloadUrl()],
            'cm:edu_metadataset' => ['mds'],
            'cm:creator' => ['Stud.IP OER-Campus'],
            'cm:autoVersion' => ['false'],
            'virtual:permalink' => [$permalink],
            'cm:versionLabel' => ['1.0'],
            'cm:versionable' => ['false'],
            'cm:created_LONG' => [(string) $material['mkdate']],
            'virtual:primaryparent_nodeid' => [$subfolder_id],
            'cm:createdISO8601' => ['2020-11-20T14:00:17.805Z'],
            'ccm:ph_action' => ['PERMISSION_ADD'],
            'cclom:general_description' => [$material['description']],
            'cm:modified' => [(string) $material['chdate']],
            'cm:edu_forcemetadataset' => ['false'],
            'cm:modifier' => ['Stud.IP OER-Campus'],
            'ccm:educationallearningresourcetype_DISPLAYNAME' => ['Übung'],
            'cm:autoVersionOnUpdateProps' => ['false'],
            "cclom:location" => ["ccrep://repo/".$material['published_id_on_twillo']],
            'ccm:educontextname' => ["default"],
            'cm:modified_LONG' => [(string) $material['chdate']],
            'ccm:questionsallowed' => ["true"],
            'cm:automaticUpdate' => ["true"],
            'cm:name' => [$material['filename']],
            'cm:initialVersion' => ["false"],
            'cclom:general_keyword' => $topics,
            'ccm:commonlicense_key' => [$material->license['twillo_licensekey']],
            'ccm:commonlicense_cc_version' => [$material->license['twillo_cclicenseversion']],
            'virtual:licenseicon' => [''],
            'virtual:licenseurl' => [''],
        ]);
        curl_setopt($cr, CURLOPT_POSTFIELDS, $postbody);
        curl_exec($cr);
        $error = curl_error($cr);
        curl_close($cr);
        if ($error) {
            return $error;
        }
        return true;
    }

    public static function deleteFromTwillo($oer_id, $user_id = null)
    {
        $user_id || $user_id = User::findCurrent()->id;

        $base = new EduSharingHelperBase(
            self::$twillo_base_url,
            file_get_contents($GLOBALS['STUDIP_BASE_PATH']."/config/twillo-private.key"),
            Config::get()->OERCAMPUS_TWILLO_APPID,
            self::getHttpProxy()// 'data-quest-Test'
        );
        $authHelper = new EduSharingAuthHelper($base);
        if (!static::$ticket) {
            static::$ticket = $authHelper->getTicketForUser(TwilloConnector::getTwilloUserID($user_id));
        }

        $header = [];
        $header[] = "Authorization: EDU-TICKET " . static::$ticket;
        $header[] = "Content-Type: application/json";
        $header[] = "Accept: application/json";

        $cr = curl_init();
        curl_setopt($cr, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($cr, CURLOPT_URL, self::$twillo_base_url . '/rest/node/v1/nodes/-home-/'.$oer_id);
        curl_setopt($cr, CURLOPT_HTTPHEADER, $header);
        curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($cr, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($cr, CURLOPT_PROXY, self::getHttpProxy());
        curl_exec($cr);
        curl_close($cr);
    }
}
