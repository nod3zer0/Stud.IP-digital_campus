<?php

/**
 *
 * @property string $id alias column for material_id
 * @property string $material_id database column
 * @property string|null $foreign_material_id database column
 * @property string|null $host_id database column
 * @property string $name database column
 * @property string $category database column
 * @property int $draft database column
 * @property string $filename database column
 * @property string|null $short_description database column
 * @property string $description database column
 * @property int $difficulty_start database column
 * @property int $difficulty_end database column
 * @property string|null $player_url database column
 * @property string|null $tool database column
 * @property string $content_type database column
 * @property string|null $front_image_content_type database column
 * @property JSONArrayObject|null $structure database column
 * @property float|null $rating database column
 * @property string $license_identifier database column
 * @property string $uri database column
 * @property string $uri_hash database column
 * @property string|null $published_id_on_twillo database column
 * @property string|null $source_url database column
 * @property JSONArrayObject|null $data database column
 * @property int $chdate database column
 * @property int $mkdate database column
 * @property SimpleORMapCollection|OERReview[] $reviews has_many OERReview
 * @property SimpleORMapCollection|OERMaterialUser[] $users has_many OERMaterialUser
 * @property OERHost|null $host belongs_to OERHost
 * @property License $license belongs_to License
 */
class OERMaterial extends SimpleORMap
{
    protected static function configure($config = [])
    {
        $config['db_table'] = 'oer_material';
        $config['belongs_to']['host'] = [
            'class_name' => OERHost::class,
            'foreign_key' => 'host_id'
        ];
        $config['has_many']['reviews'] = [
            'class_name' => OERReview::class,
            'order_by' => 'ORDER BY mkdate DESC',
            'on_delete' => 'delete'
        ];
        $config['has_many']['users'] = [
            'class_name' => OERMaterialUser::class,
            'order_by' => 'ORDER BY position ASC'
        ];
        $config['belongs_to']['license'] = [
            'class_name' => License::class,
            'foreign_key' => 'license_identifier'
        ];
        $config['serialized_fields']['structure'] = JSONArrayObject::class;
        $config['serialized_fields']['data'] = JSONArrayObject::class;
        $config['registered_callbacks']['before_store'][] = "cbHashURI";
        $config['registered_callbacks']['before_delete'][] = "cbDeleteFile";
        parent::configure($config);
    }

    public static function findAll()
    {
        return self::findBySQL("draft = '0'");
    }

    public static function findMine($user_id = null)
    {
        $user_id = $user_id ?: $GLOBALS['user']->id;
        return self::findBySQL("INNER JOIN `oer_material_users` USING (material_id)
            WHERE `oer_material_users`.user_id = ?
                AND external_contact = '0'
            ORDER BY mkdate DESC", [$user_id]
        );
    }

    public static function findByTag($tag_name)
    {
        self::fetchRemoteSearch($tag_name, true);
        $statement = DBManager::get()->prepare("
            SELECT oer_material.*
            FROM oer_material
                INNER JOIN oer_tags_material USING (material_id)
                INNER JOIN oer_tags USING (tag_hash)
                LEFT JOIN oer_hosts ON (oer_hosts.host_id = oer_material.host_id)
            WHERE oer_tags.name = :tag
                AND oer_material.draft = '0'
                AND (oer_material.host_id IS NULL OR oer_hosts.`active` = '1')
            GROUP BY oer_material.material_id
            ORDER BY oer_material.mkdate DESC
        ");
        $statement->execute(['tag' => $tag_name]);
        $material_data = $statement->fetchAll(PDO::FETCH_ASSOC);
        $materials = [];
        foreach ($material_data as $data) {
            $materials[] = OERMaterial::buildExisting($data);
        }
        return $materials;
    }

    public static function findByText($text)
    {
        self::fetchRemoteSearch($text);
        $statement = DBManager::get()->prepare("
            SELECT oer_material.*
            FROM oer_material
                LEFT JOIN oer_tags_material USING (material_id)
                LEFT JOIN oer_tags USING (tag_hash)
                LEFT JOIN oer_hosts ON (oer_hosts.host_id = oer_material.host_id)
            WHERE (
                    oer_material.name LIKE :text
                    OR description LIKE :text
                    OR short_description LIKE :text
                    OR oer_tags.name LIKE :text
                )
                AND oer_material.draft = '0'
                AND (oer_material.host_id IS NULL OR oer_hosts.`active` = '1')
            GROUP BY oer_material.material_id
            ORDER BY oer_material.mkdate DESC
        ");
        $statement->execute([
            'text' => "%".$text."%"
        ]);
        $material_data = $statement->fetchAll(PDO::FETCH_ASSOC);
        $materials = [];
        foreach ($material_data as $data) {
            $materials[] = OERMaterial::buildExisting($data);
        }
        return $materials;
    }

    public static function findByTagHash($tag_hash)
    {
        $tag = OERTag::find($tag_hash);
        if ($tag) {
            self::fetchRemoteSearch($tag['name'], true);
        }
        return self::findBySQL("INNER JOIN oer_tags_material USING (material_id)
                LEFT JOIN oer_hosts ON (oer_hosts.host_id = oer_material.host_id)
            WHERE oer_tags_material.tag_hash = ?
                AND (oer_material.host_id IS NULL OR oer_hosts.`active` = '1')
                AND draft = '0'", [$tag_hash]
        );
    }

    public static function getFileDataPath()
    {
        return $GLOBALS['OER_PATH'];
    }

    public static function getImageFileDataPath()
    {
        return $GLOBALS['OER_LOGOS_PATH'];
    }

    /**
     * Searches on remote hosts for the text.
     * @param $text
     * @param string|false $tag
     */
    public static function fetchRemoteSearch($text, $tag = false)
    {
        $cache_name = "oer_remote_searched_for_".md5($text)."_".($tag ? 1 : 0);
        $already_searched = (bool) StudipCacheFactory::getCache()->read($cache_name);
        if (!$already_searched) {
            $hosts = OERHost::findBySQL("index_server = '1' AND allowed_as_index_server = '1' ORDER BY RAND()");
            foreach ($hosts as $host) {
                if (!$host->isMe()) {
                    $host->fetchRemoteSearch($text, $tag);
                }
            }
            StudipCacheFactory::getCache()->read($cache_name, "1", 60);
        }
    }

    public static function embedOERMaterial($markup, $matches, $contents)
    {
        $id = $matches[1];
        $material = OERMaterial::find($id);

        if (!$material) {
            return _('OER Material nicht vorhanden.');
        }

        $url = $material->getDownloadUrl();

        if ($material->hasValidPreviewUrl() || $material->isPDF()) {
            if ($material->hasValidPreviewUrl()) {
                OERDownloadcounter::addCounter($material->id);
                $url = $material['player_url'];
            }
            $htmlid = "oercampus_".$material->id."_".uniqid();
            $output = "<iframe sandbox=\"allow-forms allow-popups allow-pointer-lock allow-same-origin allow-scripts\" id='".$htmlid."' src=\"". htmlReady($url). "\" style=\"width: 100%; height: 70vh; border: none;\"></iframe>";

            return $output;
        }

        $tf = new Flexi_TemplateFactory($GLOBALS['STUDIP_BASE_PATH']."/app/views");
        if ($material->hasValidPreviewUrl() || $material->isPDF()) {
            $template = $tf->open("oer/embed/url");
        } elseif ($material->isVideo()) {
            $template = $tf->open("oer/embed/video");
        } elseif ($material->isAudio()) {
            $template = $tf->open("oer/embed/audio");
        } else {
            $template = $tf->open("oer/embed/standard");
        }
        $template->url = $url;
        $template->source_url = $material['source_url'];
        $template->material = $material;
        $template->id = $id;
        return $template->render();
    }

    public function cbDeleteFile()
    {
        $this->setTopics([]);
        @unlink($this->getFilePath());
    }

    public function cbHashURI()
    {
        $this['uri_hash'] = $this['uri'] ? md5($this['uri']) : '';
    }

    public function getAuthors()
    {
        if ($this->host) {
            return $this->host->getAuthorsForMaterial($this);
        } else {
            return OERHost::thisOne()->getAuthorsForMaterial($this);
        }
    }

    public function getTopics()
    {
        $statement = DBManager::get()->prepare("
            SELECT oer_tags.*
            FROM oer_tags
                INNER JOIN oer_tags_material ON (oer_tags_material.tag_hash = oer_tags.tag_hash)
            WHERE oer_tags_material.material_id = :material_id
            ORDER BY oer_tags.name ASC
        ");
        $statement->execute(['material_id' => $this->getId()]);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function setTopics($tags)
    {
        $statement = DBManager::get()->prepare("
            DELETE FROM oer_tags_material
            WHERE material_id = :material_id
        ");
        $statement->execute(['material_id' => $this->getId()]);
        $insert_tag = DBManager::get()->prepare("
            INSERT IGNORE INTO oer_tags
            SET name = :tag,
                tag_hash = MD5(:tag)
        ");
        $add_tag = DBManager::get()->prepare("
            INSERT IGNORE INTO oer_tags_material
            SET tag_hash = MD5(:tag),
                material_id = :material_id
        ");
        foreach ((array) $tags as $tag) {
            $insert_tag->execute([
                'tag' => $tag
            ]);
            $add_tag->execute([
                'tag' => $tag,
                'material_id' => $this->getId()
            ]);
        }
    }

    public function getFilePath()
    {
        if (!$this->getId()) {
            $this->setId($this->getNewId());
        }
        return self::getFileDataPath()."/".$this->getId();
    }

    public function getDownloadUrl()
    {
        if (!$this['host_id']) {
            $base = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
            $url = $this['host_id']
                ? $this->host->url . 'download/' . $this['foreign_material_id']
                : URLHelper::getURL('dispatch.php/oer/endpoints/download/' . $this->getId());
            URLHelper::setBaseURL($base);
        } else {
            $url = $this->host->getDownloadURLForMaterial($this);
        }
        return $url;
    }

    public function getFrontImageFilePath()
    {
        if (!file_exists(self::getImageFileDataPath())) {
            mkdir(self::getImageFileDataPath());
        }
        if (!$this->getId()) {
            $this->setId($this->getNewId());
        }
        return self::getImageFileDataPath()."/".$this->getId();
    }

    public function getLogoURL()
    {
        if ($this['front_image_content_type']) {
            if ($this['host_id']) {
                return $this->host->getFrontImageURL($this);
            } else {
                return URLHelper::getURL("dispatch.php/oer/endpoints/download_front_image/".$this->getId());
            }
        } elseif ($this->isFolder()) {
            return Icon::create("folder-full", "clickable")->asImagePath();
        } elseif($this->isImage()) {
            return Icon::create("file-pic", "clickable")->asImagePath();
        } elseif($this->isPDF()) {
            return Icon::create("file-pdf", "clickable")->asImagePath();
        } elseif($this->isPresentation()) {
            return Icon::create("file-ppt", "clickable")->asImagePath();
        } elseif($this->isStudipQuestionnaire()) {
            return Icon::create("vote", "clickable")->asImagePath();
        } else {
            return Icon::create("file", "clickable")->asImagePath();
        }
    }

    /**
     * Returns an appropriate icon for the material.
     *
     * @param string $role
     * @return Icon
     */
    public function getIcon(string $role = Icon::ROLE_CLICKABLE): Icon
    {
        $icon = 'file';
        if ($this->category === 'video') {
            $icon = 'video';
        } elseif ($this->category === 'audio') {
            $icon = 'file-audio';
        } elseif ($this->category === 'presentation') {
            $icon = 'file-pdf';
        } elseif ($this->category === 'elearning') {
            $icon = 'learnmodule';
        }

        if ($this->content_type === 'application/zip') {
            $icon = 'archive3';
        }

        return Icon::create($icon, $role);
    }

    public function isFolder()
    {
        return (bool) $this['structure'];
    }

    /**
     * Checks the URL scheme of the preview URL (player_url).
     * HTTP, HTTPS, Gopher and Gemini are supported schemes.
     *
     * @return bool True, if the URL scheme matches the allowced ones,
     *     false otherwise.
     */
    public function hasValidPreviewUrl() : bool
    {
        if ($this->player_url) {
            $scheme = parse_url($this->player_url, PHP_URL_SCHEME);
            if (in_array($scheme, ['http', 'https'])) {
                return true;
            }
        }
        return false;
    }

    public function isImage()
    {
        return stripos($this['content_type'], "image") === 0;
    }

    public function isVideo()
    {
        return stripos($this['content_type'], "video") === 0;
    }

    public function isAudio()
    {
        return stripos($this['content_type'], "audio") === 0;
    }

    public function isPDF()
    {
        return $this['content_type'] === "application/pdf";
    }

    public function isPDFInlineDisplayable()
    {
        if (!$this->isPDF()) {
            return false;
        }
        $url = $this->getDownloadUrl();
        $headers = get_headers($url, true, get_default_http_stream_context($url));
        if (
            !empty($headers['Content-Disposition'])
            && substr($headers['Content-Disposition'], 0, strlen('attachment')) === 'attachment'
        ) {
            //in this case the server forces to download the document and we cannot display it in an iframe:
            return false;
        }
        return true;
    }

    protected function getFileEnding()
    {
        return pathinfo($this["filename"], PATHINFO_EXTENSION);
    }

    public function isPresentation()
    {
        return in_array($this->getFileEnding(), [
            "odp", "keynote", "ppt", "pptx"
        ]);
    }

    public function isStudipQuestionnaire()
    {
        return $this['content_type'] === "application/json+studipquestionnaire";
    }

    public function addTag($tag_name)
    {
        $tag_hash = md5($tag_name);
        if (!OERTag::find($tag_hash)) {
            $tag = new OERTag();
            $tag->setId($tag_hash);
            $tag['name'] = $tag_name;
            $tag->store();
        }
        $statement = DBManager::get()->prepare("
            INSERT IGNORE INTO oer_tags_material
            SET tag_hash = :tag_hash,
                material_id = :material_id
        ");
        return $statement->execute([
            'tag_hash' => $tag_hash,
            'material_id' => $this->getId()
        ]);
    }

    public function pushDataToIndexServers($delete = false)
    {
        $myHost = OERHost::thisOne();
        $data = [];
        $data['host'] = [
            'name' => $myHost['name'],
            'url' => $myHost['url'],
            'public_key' => $myHost['public_key']
        ];
        $data['data'] = $this->toArray();
        $data['data']['foreign_material_id'] = $data['data']['material_id'];
        unset($data['data']['material_id']);
        unset($data['data']['id']);
        unset($data['data']['user_id']);
        unset($data['data']['host_id']);
        $data['users'] = $myHost->getAuthorsForMaterial($this);
        $data['topics'] = [];
        foreach ($this->getTopics() as $tag) {
            if ($tag['name']) {
                $data['topics'][] = $tag['name'];
            }
        }
        if ($delete) {
            $data['delete_material'] = 1;
        }

        foreach (OERHost::findBySQL("index_server = '1' AND allowed_as_index_server = '1' ") as $index_server) {
            if (!$index_server->isMe()) {
                echo " push ";
                $index_server->pushDataToEndpoint("push_data", $data);
            }
        }
    }

    /**
     * Uploads this material to twillo.de. This is not actually an upload but rather a link with metadata.
     * @return bool|string : true on success, on failure a text-string as error-message
     */
    public function uploadToTwillo()
    {
        return TwilloConnector::uploadMaterial($this);
    }

    /**
     * Removes this material from twillo.de if able.
     */
    public function deleteFromTwillo()
    {
        TwilloConnector::deleteFromTwillo($this['published_id_on_twillo']);
        $this['published_id_on_twillo'] = null;
        $this->store();
    }

    public function fetchData()
    {
        if ($this['host_id'] && $this->host) {
            $data = $this->host->fetchItemData($this);

            if (!$data) {
                return false;
            }

            if (!empty($data['deleted'])) {
                return "deleted";
            }

            //material:
            $material_data = $data['data'];
            unset($material_data['material_id']);
            unset($material_data['mkdate']);
            $this->setData($material_data);
            $this->store();

            //topics:
            $this->setTopics($data['topics'] ?? []);

            //user:
            $this->setUsers($data['users'] ?? []);
            $reviews = $data['reviews'] ?? [];
            foreach ($reviews as $review_data) {
                $currenthost = OERHost::findOneByUrl(trim($review_data['host']['url']));
                if (!$currenthost) {
                    $currenthost = new OERHost();
                    $currenthost['url'] = trim($review_data['host']['url']);
                    $currenthost['last_updated'] = time();
                    $currenthost->fetchPublicKey();
                    if ($currenthost['public_key']) {
                        $currenthost->store();
                    }
                }
                if ($currenthost && $currenthost['public_key'] && !$currenthost->isMe()) {
                    $review = OERReview::findOneBySQL("
                            context_id = :context_id
                            AND `metadata` LIKE :foreign_review_id
                            AND `metadata` LIKE :host_id", [
                        'context_id' => $this->getId(),
                        'foreign_review_id' => "%".$review_data['foreign_review_id']."%",
                        'host_id' => "%".$currenthost->getId()."%"
                    ]);
                    if (!$review) {
                        $review = new OERReview();
                        $review['context_id'] = $this->getId();
                        $review['context_type'] = "public";
                        $review['display_class'] = "OERReview";
                        $review['visible_in_stream'] = 0;
                        $review['commentable'] = 1;
                    }
                    $review['content'] = $review_data['review'];
                    $review['metadata'] = [
                        'host_id' => $currenthost->getId(),
                        'foreign_review_id' => $review_data['foreign_review_id'],
                        'rating' => $review_data['rating']
                    ];
                    if ($review_data['chdate']) {
                        $review['chdate'] = $review_data['chdate'];
                    }
                    if ($review_data['mkdate']) {
                        $review['mkdate'] = $review_data['mkdate'];
                    }

                    $user = ExternalUser::findOneBySQL("foreign_id = :foreign_id AND host_id = :host_id", [
                        'foreign_id' => $review_data['user']['user_id'],
                        'host_id' => $currenthost->getId()
                    ]);

                    if (!$user) {
                        $user = new ExternalUser();
                        $user['foreign_id'] = $review_data['user']['user_id'];
                        $user['host_id'] = $currenthost->getId();
                    }
                    $user['contact_type'] = "oercampus";
                    $user['name'] = $review_data['user']['name'];
                    $user['avatar_url'] = $review_data['user']['avatar'] ?: null;
                    $user['data'] = [
                        'description' => $review_data['user']['description'] ?: ''
                    ];
                    $user->store();

                    $review['user_id'] = $user->getId();

                    $review->store();
                }
            }
        }
        return true;
    }

    public function setUsers($data)
    {
        $old_user_ids = $this->users->pluck("user_id");
        $current_user_ids = [];
        foreach ((array) $data as $index => $userdata) {
            $userhost = OERHost::findOneBySQL("url = ?", [$userdata['host_url']]);
            if ($userhost->isMe()) {
                $user = User::find($userdata['user_id']);
                $materialuser = OERMaterialUser::findOneBySQL("material_id = ? AND user_id = ? AND external_contact = '0'", [$this->getId(), $user->getId()]);
                if (!$materialuser) {
                    $materialuser = new OERMaterialUser();
                    $materialuser['user_id'] = $user->getId();
                    $materialuser['material_id'] = $this->getId();
                    $materialuser['external_contact'] = 0;
                }
                $materialuser['position'] = $index + 1;
                $materialuser->store();
                $current_user_ids[] = $user->getId();
            } else {
                $user = ExternalUser::findOneBySQL("contact_type = 'oercampus' AND foreign_id = ? AND host_id = ?", [
                    $userdata['user_id'],
                    $userhost->getId()
                ]);
                if (!$user) {
                    $user = new ExternalUser();
                    $user['foreign_id'] = $userdata['user_id'];
                    $user['host_id'] = $userhost->getId();
                    $user['contact_type'] = "oercampus";
                }
                $user['name'] = $userdata['name'];
                $user['avatar_url'] = $userdata['avatar'] ?: null;
                $userdata = $user['data'] ? $user['data']->getArrayCopy() : [];
                $userdata['description'] = $userdata['description'] ?: null;
                $user['data'] = $userdata;
                $user->store();

                $materialuser = OERMaterialUser::findOneBySQL("material_id = ? AND user_id = ? AND external_contact = '1'", [$this->getId(), $user->getId()]);
                if (!$materialuser) {
                    $materialuser = new OERMaterialUser();
                    $materialuser['user_id'] = $user->getId();
                    $materialuser['material_id'] = $this->getId();
                    $materialuser['external_contact'] = 1;
                }
                $materialuser['position'] = $index + 1;
                $materialuser->store();
                $current_user_ids[] = $user->getId();
            }
        }
        foreach (array_diff($old_user_ids, $current_user_ids) as $deletable_user_id) {
            OERMaterialUser::deleteBySQL("material_id = ? AND user_id = ?", [$this->getId(), $deletable_user_id]);
        }
    }

    public function calculateRating()
    {
        $rating = 0;
        $factors = 0;
        foreach ($this->reviews as $review) {
            $age = time() - $review['chdate'];
            $factor = (pi() - 2 * atan($age / (86400 * 180))) / pi();
            $rating += $review['metadata']['rating'] * $factor * 2;
            $factors += $factor;
        }
        if ($factors > 0) {
            $rating /= $factors;
        } else {
            return $rating = null;
        }
        return $rating;
    }

    public function isMine()
    {
        $user = OERMaterialUser::findOneBySQL("material_id = ? AND external_contact = '0' AND user_id = ?", [$this->getId(), $GLOBALS['user']->id]);
        return $user ? true : false;
    }

    public function notifyFollowersAboutNewMaterial()
    {
        $statement = DBManager::get()->prepare("
            SELECT DISTINCT auth_user_md5.username
            FROM oer_abo
                INNER JOIN auth_user_md5 USING (user_id)
            WHERE material_id IS NULL
                AND user_id != ?
        ");
        $statement->execute([$GLOBALS['user']->id]);
        $usernames = $statement->fetchAll(PDO::FETCH_COLUMN, 0);
        $messsaging = new messaging();
        $subject = _("Neues Material im OER Campus");
        if ($this->users[0]) {
            $user_name = $this->users[0]['external_contact']
                ? $this->users[0]['oeruser']['name']
                : User::find($this->users[0]['user_id'])->getFullname();
        } else {
            $user_name = _("unbekannt");
        }
        $oldbase = URLHelper::setBaseURL($GLOBALS['ABSOLUTE_URI_STUDIP']);
        $message = sprintf(
            _('%1$s hat soeben neues Material auf dem OER Campus zur verfügung gestellt. Viel Spaß! <br> %2$s'),
            $user_name,
            URLHelper::getURL("dispatch.php/oer/market/details/".$this->getId())
        );
        URLHelper::setBaseURL($oldbase);
        $messsaging->insert_message(
            $message,
            $usernames,
            '____%system%____',
            '',
            '',
            '',
            '',
            $subject,
            '',
            'normal',
            [_('OER Campus')],
            false
        );
    }

    public function autoDetectCategory()
    {
        if (substr($this['content_type'], 0, 5) === "video") {
            return "video";
        } elseif (substr($this['content_type'], 0, 5) === "audio") {
            return "audio";
        } elseif ($this['player_url']) {
            return "elearning";
        } elseif (in_array($this['content_type'], ['application/pdf', 'application/x-iwork-keynote-sffkey', 'application/vnd.apple.keynote', 'application/vnd.oasis.opendocument.presentation', 'application/vnd.oasis.opendocument.presentation-template']) || (stripos($this['content_type'], 'application/vnd.openxmlformats-officedocument.presentationml.') === 0) || (stripos($this['content_type'], 'application/vnd.ms-powerpoint') === 0)) {
            return "presentation";
        }
        return "";
    }
}
