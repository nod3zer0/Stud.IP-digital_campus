<?php

/**
 * @license GPL2 or any later version
 *
 * @property string $id alias column for host_id
 * @property string $host_id database column
 * @property string $sorm_class database column
 * @property string $name database column
 * @property string $url database column
 * @property string $public_key database column
 * @property string|null $private_key database column
 * @property int $active database column
 * @property int $index_server database column
 * @property int $allowed_as_index_server database column
 * @property int $last_updated database column
 * @property int $chdate database column
 * @property int $mkdate database column
 */
class OERHost extends OERIdentity
{

    //These two HTTP-headers are non-conformant custom HTTP-headers for requests
    const OER_HEADER_PUBLIC_KEY_HASH = "Publickey-Hash";
    const OER_HEADER_SIGNATURE = "RSA-Signature-Base64";

    /**
     * Fetches the OERHost of this Stud.IP. If none existed before it will be created and the object returnd.
     * @return OERHost
     */
    public static function thisOne()
    {
        $host = self::findOneBySQL("`private_key` IS NOT NULL AND `sorm_class` = 'OERHost' LIMIT 1");
        if ($host) {
            $host['url'] = $GLOBALS['oer_PREFERRED_URI'] ?? $GLOBALS['ABSOLUTE_URI_STUDIP']."dispatch.php/oer/endpoints/";
            if ($host->isFieldDirty("url")) {
                $host->store();
            }
            return $host;
        } else {
            $host = new self();
            $host['name'] = Config::get()->UNI_NAME_CLEAN;
            $host['url'] = $GLOBALS['oer_PREFERRED_URI'] ?? $GLOBALS['ABSOLUTE_URI_STUDIP']."dispatch.php/oer/endpoints/";
            $host['last_updated'] = time();
            $host->store();
            return $host;
        }
    }

    /**
     * Fetches and returns all known hosts from database.
     * @return array of all OERHost objects
     */
    public static function findAll()
    {
        return self::findBySQL("1=1 ORDER BY name ASC");
    }

    public static function URIExists($uri)
    {
        return (bool) OERMaterial::findOneBySQL("LEFT JOIN `oer_hosts` USING (`host_id`) WHERE (`oer_hosts`.`sorm_class` = 'OERHost' OR `oer_hosts`.`sorm_class` IS NULL) AND `uri_hash` = ?", [
            md5($uri)
        ]);
    }

    public static function findBySQL($sql, $params = [])
    {
        $hosts = parent::findBySQL($sql, $params);
        foreach ($hosts as $key => $host) {
            $class = $host['sorm_class'];
            if ($class && ($class !== 'OERHost') && is_subclass_of($class, 'OERHost')) {
                $data = $host->toRawArray();
                $host = $class::buildExisting($data);
                $hosts[$key] = $host;
            }
        }
        return $hosts;
    }

    public static function find($id)
    {
        $host = parent::find($id);

        if ($host) {
            $class = $host['sorm_class'];
            if ($class && ($class !== 'OERHost') && is_subclass_of($class, 'OERHost')) {
                $data = $host->toRawArray();
                $host = $class::buildExisting($data);
            }
        }
        return $host;
    }

    /**
     * configures this class
     * @param array $config
     */
    protected static function configure($config = [])
    {
        $config['db_table'] = 'oer_hosts';
        parent::configure($config);
    }

    /**
     * Checks if this object is the OERHost of this Stud.IP.
     * @return bool
     */
    public function isMe()
    {
        return (bool) $this['private_key'];
    }

    /**
     * Fetches the public key from an OERHost. Makes a request to the other host.
     */
    public function fetchPublicKey()
    {
        $endpoint_url = $this['url']."fetch_public_host_key";
        $endpoint_url .= "?from=".urlencode($GLOBALS['oer_PREFERRED_URI'] ?? $GLOBALS['ABSOLUTE_URI_STUDIP']."dispatch.php/oer/endpoints/");
        $host_data = @file_get_contents($endpoint_url, false, get_default_http_stream_context($endpoint_url));
        if ($host_data) {
            $host_data = json_decode($host_data, true);
            if ($host_data) {

                $this['name'] = $host_data['name'];
                $this['public_key'] = preg_replace("/\r/", "", $host_data['public_key']);
                $this['url'] = $host_data['url'];
                $this['index_server'] = $host_data['index_server'];
                $host['last_updated'] = time();
                if ($this->isNew()) {
                    $host['active'] = 1;
                }
            }
        }
    }

    /**
     * Ask this host for all hosts that it knows. This is a request to the other host.
     */
    public function askKnownHosts()
    {
        $endpoint_url = $this['url']."fetch_known_hosts"
            ."?from=".urlencode($GLOBALS['oer_PREFERRED_URI'] ?? $GLOBALS['ABSOLUTE_URI_STUDIP']."dispatch.php/oer/endpoints/");
        $output = @file_get_contents($endpoint_url, false, get_default_http_stream_context($endpoint_url));
        if ($output) {
            $output = json_decode($output, true);
            foreach ((array) $output['hosts'] as $host_data) {
                $host = OERHost::findOneByUrl($host_data['url']);
                if (!$host) {
                    $host = new OERHost();
                    $host['url'] = $host_data['url'];
                }
                if ($host['last_updated'] < time() - 60 * 60 * 8) {
                    $host->fetchPublicKey();
                }
                $host->store();
            }
        } else {
            PageLayout::postWarning(_("Kann von dem Server keine Daten bekommen."));
        }
        return $output;
    }

    /**
     * Executes a search request on the host.
     * @param string|null $text : the search string
     * @param string|null $tag : a tag to search for
     */
    public function fetchRemoteSearch($text = null, $tag = null)
    {
        $endpoint_url = $this['url']."search_items";
        if ($tag) {
            $endpoint_url .= "?tag=".urlencode($tag);
        } else {
            $endpoint_url .= "?text=".urlencode($text);
        }
        $output = @file_get_contents($endpoint_url, false, get_default_http_stream_context($endpoint_url));
        if ($output) {
            $output = json_decode($output, true);
            foreach ((array) $output['results'] as $material_data) {
                $host = OERHost::findOneBySQL("public_key = ?", [$material_data['host']['public_key']]);
                if (!$host) {
                    $host = new OERHost();
                    $host['url'] = $material_data['host']['url'];
                    $host->fetchPublicKey();
                    $host->store();
                }
                if (!$host->isMe()) {
                    //set material:
                    $material_data['data']['foreign_material_id'] = $material_data['data']['id'];
                    $material = OERMaterial::findOneBySQL("foreign_material_id = ? AND host_id = ?", [
                        $material_data['data']['foreign_material_id'],
                        $host->getId()
                    ]);
                    if (!$material) {
                        $material = new OERMaterial();
                    }
                    unset($material_data['data']['id']);
                    $usersdata = $material_data['data']['users'];
                    unset($material_data['data']['users']);
                    $material->setData($material_data['data']);
                    if (!$material['category']) {
                        $material['category'] = $material->autoDetectCategory();
                    }
                    $material['host_id'] = $host->getId();
                    $material->store();

                    //set topics:
                    $material->setUsers($usersdata);

                    //set topics:
                    $material->setTopics($material_data['topics']);
                }
            }
        }
    }

    /**
     * Pushes some data to the foreign OERHost.
     * @param string $endpoint : part behind the host-url like "push_data"
     * @param array $data : the data to be pushed as an associative array
     * @return bool|CurlHandle|resource
     */
    public function pushDataToEndpoint($endpoint, $data)
    {
        $payload = json_encode($data);

        $myHost = OERHost::thisOne();
        $endpoint_url = $this['url'].$endpoint;

        $request = curl_init();
        curl_setopt($request, CURLOPT_URL, $endpoint_url);
        curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($request, CURLOPT_VERBOSE, 0);
        curl_setopt($request, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($request, CURLOPT_POST, true);
        curl_setopt($request, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($request, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($request, CURLOPT_SSL_VERIFYHOST, false);

        $header = [
            OERHost::OER_HEADER_SIGNATURE.": ".base64_encode($myHost->createSignature($payload)),
            OERHost::OER_HEADER_PUBLIC_KEY_HASH.": ".md5($myHost['public_key'])
        ];
        curl_setopt($request, CURLOPT_HTTPHEADER, $header);

        $result = curl_exec($request);
        $response_code = curl_getinfo($request, CURLINFO_HTTP_CODE);
        curl_close($request);
        return $response_code < 300;
    }

    /**
     * Fetches all information of an item from that host. This is a request.
     * @param string $foreign_material_id : foreign id of that oer-material
     * @return array|null : data of that material or null on error.
     */
    public function fetchItemData(OERMaterial $material)
    {
        $endpoint_url = $this['url']."get_item_data/".urlencode($material['foreign_material_id']);
        $output = @file_get_contents($endpoint_url, false, get_default_http_stream_context($endpoint_url));
        if ($output) {
            $output = json_decode($output, true);
            if ($output) {
                return $output;
            }
        }
    }

    public function getFrontImageURL(OERMaterial $material)
    {
        return $this['url']."download_front_image/".$material['foreign_material_id'];
    }

    public function isReviewable()
    {
        return true;
    }

    public function getAuthorsForMaterial(OERMaterial $material)
    {
        $users = [];
        foreach ($material->users as $materialdata) {
            if ($materialdata['oeruser']) {
                $user = $materialdata['oeruser'];

                $users[] = [
                    'user_id' => $user['foreign_id'],
                    'name' => $user['name'],
                    'avatar' => $user['avatar_url'],
                    'description' => $user['data']['description'],
                    'host_url' => $user->host['url'],
                    'link' => URLHelper::getURL('dispatch.php/oer/market/profile/' . $user->getId()),
                    'hostname' => $this['name']
                ];
            } else {
                $user = User::find($materialdata['user_id']);
                $users[] = [
                    'user_id' => $user['user_id'],
                    'name' => $user ? $user->getFullName() : _('unbekannt'),
                    'avatar' => Avatar::getAvatar($user['user_id'])->getURL(Avatar::NORMAL),
                    'description' => $user ? $user['oercampus_description'] : '',
                    'host_url' => OERHost::thisOne()->url,
                    'link' => URLHelper::getURL('dispatch.php/profile', ['username' => $user['username']]),
                    'hostname' => $this['name']
                ];
            }
        }
        return $users;
    }

    public function getDownloadURLForMaterial(OERMaterial $material)
    {
        $base = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
        $url = $material['host_id']
            ? $this->url . 'download/' . $material['foreign_material_id']
            : URLHelper::getURL('dispatch.php/oer/endpoints/download/' . $material->getId());
        URLHelper::setBaseURL($base);
        return $url;
    }
}
