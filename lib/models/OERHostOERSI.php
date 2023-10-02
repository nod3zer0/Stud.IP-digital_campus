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
class OERHostOERSI extends OERHost
{

    /**
     * Executes a search request on the host.
     * @param string|null $text : the search string
     * @param string|null $tag : a tag to search for
     */
    public function fetchRemoteSearch($text = null, $tag = null)
    {
        $endpoint_url = 'https://oersi.org/resources/api/search/oer_data/_search';
        $appendix = Config::get()->OER_OERSI_ONLY_DOWNLOADABLE ? ' AND _exists_:encoding.contentUrl' : '';
        if ($tag) {
            $endpoint_url .= '?q=' . urlencode("keywords:" . $tag . $appendix);
        } else {
            $endpoint_url .= '?q=' . urlencode($text . $appendix);
        }
        $cr = curl_init();
        curl_setopt($cr, CURLOPT_URL, $endpoint_url);
        curl_setopt($cr, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($cr, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($cr, CURLOPT_SSL_VERIFYHOST, false);
        $stream_context_options = stream_context_get_options(get_default_http_stream_context($endpoint_url));
        curl_setopt($cr, CURLOPT_PROXY, isset($stream_context_options['http']['proxy']) ? Config::get()->HTTP_PROXY : '');
        $output = curl_exec($cr);
        $error = curl_error($cr);
        if ($error) {
            $error_number = curl_getinfo($cr,    CURLINFO_HTTP_CODE);
            error_log('OERSI search is not working. '.$error_number.': '.$error);
        }
        curl_close($cr);
        if ($output) {
            $json = json_decode($output, true);
            if ($json !== null) {
                foreach ((array)$json['hits']['hits'] as $material_data) {
                    //check if material already in database from this or another OER Campus host
                    if (OERHost::URIExists($material_data['_source']['id'])) {
                        continue;
                    }

                    $material = OERMaterial::findOneBySQL('foreign_material_id = ? AND host_id = ?', [
                        md5($material_data['_source']['id']),
                        $this->getId()
                    ]);
                    if (!$material) {
                        $material = new OERMaterial();
                        $material['foreign_material_id'] = md5($material_data['_source']['id']);
                        $material['host_id'] = $this->getId();
                    }
                    $material['name'] = mb_substr($material_data['_source']['name'], 0, 64);
                    $material['draft'] = '0';
                    $material['filename'] = '';
                    $material['short_description'] = '';
                    $material['description'] = $material_data['_source']['description'] ?: '';
                    $material['difficulty_start'] = 0;
                    $material['difficulty_end'] = 12;
                    $material['uri'] = $material_data['_source']['id'];
                    $material['source_url'] = $material_data['_source']['id'];
                    $material['content_type'] = $material_data['_source']['encoding'][0]['encodingFormat'] ?: '';
                    $material['license_identifier'] = $this->getLicenseID($material_data['_source']['license']['id']) ?: '';
                    if (!$material['category']) {
                        $material['category'] = $material->autoDetectCategory();
                    }
                    $material['front_image_content_type'] = $material_data['_source']['image'] ? 'image/jpg' : null;
                    $material['data'] = [
                        'front_image_url' => $material_data['_source']['image'],
                        'download' => $material_data['_source']['encoding'][0]['contentUrl'] ?: '',
                        'id' => $material_data['_id'],
                        'organization' => $material_data['_source']['sourceOrganization'][0]['name'] ?: $material_data['_source']['publisher'][0]['name']
                    ];
                    $material->store();

                    //set users:
                    $userdata = [];
                    foreach ((array) $material_data['_source']['creator'] as $creator) {
                        $userdata[] = [
                            'user_id' => md5($creator['name']),
                            'name' => $creator['name'],
                            'host_url' => $this['url']
                        ];
                    }
                    $material->setUsers($userdata);

                    //set topics:
                    $material->setTopics($material_data['_source']['keywords']);

                }
            } else {
                error_log('OERSI returns bad JSON data: '.$output);
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
        return true;
    }

    /**
     * Fetches all information of an item from that host. This is a request.
     * @param string $foreign_material_id : foreign id of that oer-material
     * @return array|null : data of that material or null on error.
     */
    public function fetchItemData(OERMaterial $material)
    {
        $endpoint_url = 'https://oersi.org/resources/' . urlencode($material['data']['id']) . '?format=json';
        $output = @file_get_contents($endpoint_url, false, get_default_http_stream_context($endpoint_url));
        if ($output) {
            $output = json_decode($output, true);
            if ($output) {
                $data = [];
                $data['name'] = mb_substr($output['name'], 0, 64);
                $data['draft'] = '0';
                $data['filename'] = '';
                $data['short_description'] = '';
                $data['description'] = $output['description'] ?? '';
                $data['difficulty_start'] = 12;
                $data['uri'] = $output['encoding'][0]['contentUrl'] ?? '';
                $data['source_url'] = $output['id'];
                $data['content_type'] = $output['encoding'][0]['encodingFormat'] ?? '';
                $data['license_identifier'] = $this->getLicenseID($output['license']['id']) ?: '';
                if (empty($data['category'])) {
                    $data['category'] = $material->autoDetectCategory();
                }
                $data['front_image_content_type'] = !empty($output['image']) ? 'image/jpg' : null;
                $data['data'] = $material['data']->getArrayCopy();
                $data['data']['download'] = $output['encoding'][0]['contentUrl'] ?? '';
                $data['data']['front_image_url'] = $output['image'] ?? '';
                $data['data']['organization'] = $output['sourceOrganization'][0]['name'] ?? $output['publisher'][0]['name'] ?? '';
                return [
                    'data' => $data,
                    'topics' => $output['keywords'] ?? []
                ];
            }
        } else {
            return ['deleted' => 1];
        }
    }

    public function getFrontImageURL(OERMaterial $material)
    {
        return $material['data'] ? $material['data']['front_image_url'] : null;
    }

    /**
     * Tries to match the CC-license URL from OERSI to an spdx-identifier, which is used in Stud.IP
     * @param $license : an URL
     * @return string|null
     */
    protected function getLicenseID($license)
    {
        $matched = preg_match("^https:\/\/creativecommons.org\/licenses\/([\w\d\-\.]+)\/([\w\d\-\.]+)^", $license, $matches);
        if ($matched) {
            $spdx_id = 'CC-' . strtoupper($matches[1]) . '-' . strtoupper($matches[2]);
            if (License::find($spdx_id)) {
                return $spdx_id;
            }
        }
        return null;
    }

    public function isReviewable()
    {
        return false;
    }

    public function getDownloadURLForMaterial(OERMaterial $material)
    {
        return $material['data']['download'] ?? $material['uri'];
    }

    public function cbCreateKeysIfNecessary()
    {
        //do nothing
    }


}
