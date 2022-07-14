<?php

class OERHostOERSI extends OERHost
{

    /**
     * Executes a search request on the host.
     * @param string|null $text : the search string
     * @param string|null $tag : a tag to search for
     */
    public function fetchRemoteSearch($text = null, $tag = null)
    {
        $endpoint_url = 'https://oersi.de/resources/api-internal/search/oer_data/_search';
        $appendix = Config::get()->OER_OERSI_ONLY_DOWNLOADABLE ? ' AND _exists_:encoding.contentUrl' : '';
        if ($tag) {
            $endpoint_url .= '?q=' . urlencode("keywords:" . $tag . $appendix);
        } else {
            $endpoint_url .= '?q=' . urlencode($text . $appendix);
        }
        $output = @file_get_contents($endpoint_url);
        if ($output) {
            $output = json_decode($output, true);
            foreach ((array) $output['hits']['hits'] as $material_data) {
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
                $material['difficulty_start'] = 12;
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
                    'authors' => $material_data['_source']['creator'],
                    'organization' => $material_data['_source']['sourceOrganization'][0]['name'] ?: $material_data['_source']['publisher'][0]['name']
                ];
                $material->store();

                //set topics:
                //$material->setUsers([]);

                //set topics:
                $material->setTopics($material_data['_source']['keywords']);

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
        $endpoint_url = 'https://oersi.de/resources/' . urlencode($material['data']['id']) . '?format=json';
        $output = @file_get_contents($endpoint_url);
        if ($output) {
            $output = json_decode($output, true);
            if ($output) {
                $data = [];
                $data['name'] = mb_substr($output['name'], 0, 64);
                $data['draft'] = '0';
                $data['filename'] = '';
                $data['short_description'] = '';
                $data['description'] = $output['description'] ?: '';
                $data['difficulty_start'] = 0;
                $data['difficulty_start'] = 12;
                $data['uri'] = $output['encoding'][0]['contentUrl'] ?: '';
                $data['source_url'] = $output['id'];
                $data['content_type'] = $output['encoding'][0]['encodingFormat'] ?: '';
                $data['license_identifier'] = $this->getLicenseID($output['license']['id']) ?: '';
                if (!$data['category']) {
                    $data['category'] = $material->autoDetectCategory();
                }
                $data['front_image_content_type'] = $output['image'] ? 'image/jpg' : null;
                $data['data'] = $material['data']->getArrayCopy();
                $data['data']['download'] = $output['encoding'][0]['contentUrl'] ?: '';
                $data['data']['front_image_url'] = $output['image'];
                $data['data']['authors'] = $output['creator'];
                $data['data']['organization'] = $output['sourceOrganization'][0]['name'] ?: $output['publisher'][0]['name'];
                $data = [
                    'data' => $data,
                    'topics' => $output['keywords']
                ];
                return $data;
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
        preg_match("^https:\/\/creativecommons.org\/licenses\/([\w\d\-\.]+)\/([\w\d\-\.]+)^", $license, $matches);
        if ($matches[0]) {
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

    public function getAuthorsForMaterial(OERMaterial $material)
    {
        $users = [];
        $data = $material->data->getArrayCopy();
        foreach ((array) $data['authors'] as $author) {
            $users[] = [
                'name' => $author['name'],
                'hostname' => $data['organization'] ?: $this['name']
            ];
        }
        return $users;
    }

    public function getDownloadURLForMaterial(OERMaterial $material)
    {
        return $material['uri'];
    }


}
