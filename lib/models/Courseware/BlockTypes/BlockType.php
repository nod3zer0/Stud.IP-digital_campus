<?php

namespace Courseware\BlockTypes;

use Courseware\CoursewarePlugin;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Validator;

/**
 * This class represents the content of a Courseware block stored in payload.
 *
 * @author  Marcus Eibrink-Lunzenauer <lunzenauer@elan-ev.de>
 * @author  Till Glöggler <gloeggler@elan-ev.de>
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class BlockType
{
    /**
     * Returns a short string describing this type of blocks.
     *
     * @return string the short string describing this type
     */
    abstract public static function getType(): string;

    /**
     * Returns the title of this type of blocks suitable to display it to the user.
     *
     * @return string the title of this type of blocks
     */
    abstract public static function getTitle(): string;

    /**
     * Returns the description of this type of blocks suitable to display it to the user.
     *
     * @return string the description of this type of blocks
     */
    abstract public static function getDescription(): string;

    /**
     * Returns the initial payload of every instance of this type of block.
     *
     * @return array<mixed> the initial payload of an instance of this type of block
     */
    abstract public function initialPayload(): array;

    /**
     * Returns the JSON schema which is used to validate the payload of
     * instances of this type of block.
     *
     * @return Schema the JSON schema to be used
     */
    abstract public static function getJsonSchema(): Schema;

    /**
     * Returns a list of categories to which this type of block is associated.
     *
     * @return array the list of categories
     */
    abstract public static function getCategories(): array;

    /**
     * Returns a list of content types to which this type of block is associated.
     *
     * @return array the list of content types
     */
    abstract public static function getContentTypes(): array;

    /**
     * Returns a list of file types to which this type of block is associated.
     *
     * @return array the list of file types
     */
    abstract public static function getFileTypes(): array;

    /**
     * Returns a list of tags to which this type of block is associated.
     * 
     * @return array the list of tags
     */
    public static function getTags(): array
    {
        return ['unspecified'];
    }

    /**
     * Returns all known types of containers: core types and plugin types as well.
     *
     * @return array<string> a list of all known types of blocks;
     *                       each one a fully qualified class name
     */
    public static function getBlockTypes(): array
    {
        $blockTypes = [
            Audio::class,
            BeforeAfter::class,
            BiographyAchievements::class,
            BiographyCareer::class,
            BiographyGoals::class,
            BiographyPersonalInformation::class,
            Canvas::class,
            Chart::class,
            Code::class,
            Confirm::class,
            Date::class,
            DialogCards::class,
            Document::class,
            Download::class,
            Embed::class,
            Folder::class,
            Gallery::class,
            Headline::class,
            IFrame::class,
            ImageMap::class,
            KeyPoint::class,
            Link::class,
            Lti::class,
            TableOfContents::class,
            Text::class,
            Timeline::class,
            Typewriter::class,
            Video::class,
        ];

        // try {
            foreach (\PluginEngine::getPlugins(CoursewarePlugin::class) as $plugin) {
                $blockTypes = $plugin->registerBlockTypes($blockTypes);
            }
        // } catch (\Exception $e) {
        //     // there is nothing we can do here other than absorbing exceptions
        // }

        return $blockTypes;
    }

    /**
     * @param string $blockType a short string describing a type of block; see `getType`
     *
     * @return bool true, if the given type of block is valid; false otherwise
     */
    public static function isBlockType(string $blockType): bool
    {
        return null !== self::findBlockType($blockType);
    }

    /**
     * Returns the classname of a block type whose `type` equals the given one.
     *
     * @param string $blockType a short string describing a type of block; see `getType`
     *
     * @return mixed either the classname if the given type was valid; null otherwise
     */
    public static function findBlockType(string $blockType): ?string
    {
        foreach (self::getBlockTypes() as $class) {
            if ($class::getType() === $blockType) {
                return $class;
            }
        }

        return null;
    }

    /**
     * Creates an instance of BlockType for a given block.
     *
     * @param \Courseware\Block $block the block whose BlockType is returned
     *
     * @return BlockType the BlockType associated with the given block
     */
    public static function factory(\Courseware\Block $block): BlockType
    {
        if (!($class = self::findBlockType($block['block_type']))) {
            // TODO: Hier müsste es eine weniger allgemeine Exception geben.
            // throw new \RuntimeException('Invalid `block_type` attribute in database.');
            return new \Courseware\BlockTypes\Error($block);
        }

        return new $class($block);
    }

    /**
     * Validates a given payload according to the JSON schema of this type of block.
     *
     * @param mixed $payload the payload to be validated
     *
     * @return bool true, if the given payload is valid; false otherwise
     */
    public function validatePayload($payload): bool
    {
        $schema = static::getJsonSchema();
        $validator = new Validator();
        $result = $validator->schemaValidation($payload, $schema);

        return $result->isValid();
    }

    /** @var \Courseware\Block */
    protected $block;

    /**
     * @param \Courseware\Block $block the block associated to this type
     */
    public function __construct(\Courseware\Block $block)
    {
        $this->block = $block;
    }

    /**
     * Returns the decoded payload of the block associated with this instance.
     *
     * @return mixed the decoded payload
     */
    public function getPayload()
    {
        $decoded = $this->decodePayloadString($this->block['payload']);

        return $decoded;
    }

    /**
     * Encodes the payload and sets it in the associated block.
     *
     * @param mixed $payload the payload to be encoded
     */
    public function setPayload($payload): void
    {
        $this->block['payload'] = null === $payload ? null : json_encode($payload, true);
    }

    // TODO: (tgloeggl) DocBlock ergänzen
    public function copyPayload(string $rangeId = ''): array
    {
        return $this->getPayload();
    }

    /**
     * Returns a list of files associated to the block.
     *
     * @return array the list of files
     */
    public function getFiles(): array
    {
        return [];
    }

    /**
     * Decode a given payload.
     *
     * @param string $payload the payload to be decoded
     *
     * @return mixed the decoded payload
     */
    protected function decodePayloadString(string $payload)
    {
        if ('' === $payload) {
            $decoded = $this->initialPayload();
        } else {
            $decoded = json_decode($payload, true);
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \RuntimeException('TODO');
            }
        }

        return $decoded;
    }

    /**
     * Returns all feedback of the block.
     *
     * @return array<array> a list of feedback objects
     */
    public function getFeedback(): array
    {
        $feedbacks = [];
        foreach ($this->block['feedback'] as $item) {
            if ($item['user_id']) {
                $item['user_name'] = \User::find($item['user_id'])->getFullname();
                $item['user_avatar'] = \Avatar::getAvatar($item['user_id'])->getImageTag(\Avatar::SMALL);
            }
            array_push($feedbacks, $item);
        }

        return $feedbacks;
    }

    /**
     * Casts a \FileRef of a given ID to an array and returns that.
     *
     * @param string $fileId the ID of the \FileRef
     *
     * @return array either an empty array if there is no such \FileRef or
     *               that \FileRef cast to an array
     */
    protected function getFileById(string $fileId)
    {
        $file_ref = \FileRef::find($fileId);
        $user = \User::findCurrent();

        if ($file_ref && $file_ref->getFileType()->isDownloadable($user->id)) {
            return $file_ref->toArray();
        } else {
            return [];
        }
    }

    protected function getFileRefById(string $fileId)
    {
        $file_ref = \FileRef::find($fileId);
        $user = \User::findCurrent();

        if ($file_ref && $file_ref->getFileType()->isDownloadable($user->id)) {
            return $file_ref;
        } else {
            return null;
        }
    }

    /**
     * Copies a file to a specified range.
     *
     * @param string $fileId  the ID of the file
     * @param string $rangeId the ID of the range
     *
     * @return string the ID of the copy
     */
    protected function copyFileById(string $fileId, string $rangeId): string
    {
        static $file_map = [];

        $file_ref = \FileRef::find($fileId);

        if (!$file_ref) {
            return '';
        }

        if ($file_ref->getRangeCourseId() === $rangeId) {
            return $fileId;
        }

        if (isset($file_map[$fileId])) {
            return $file_map[$fileId];
        }

        $user = \User::findCurrent();
        $destinationFolder = $this->getDestinationFolder($user, $rangeId);
        $copiedFile = \FileManager::copyFile(
            $file_ref->getFiletype(),
            $destinationFolder,
            $user
        );

        if (is_object($copiedFile)) {
            return $file_map[$fileId] = $copiedFile->id;
        }

        return '';
    }

    /**
     * Copies a folder to a specified range.
     *
     * @param string $fileId  the ID of the folder
     * @param string $rangeId the ID of the range
     *
     * @return string the ID of the copy
     */
    protected function copyFolderById(string $folderId, string $rangeId): string
    {
        static $folder_map = [];

        $sourceFolder = \Folder::find($folderId);

        if (!$sourceFolder) {
            return '';
        }

        if ($sourceFolder->getRangeCourseId() === $rangeId) {
            return $folderId;
        }

        if (isset($folder_map[$folderId])) {
            return $folder_map[$folderId];
        }

        $user = \User::findCurrent();
        $destinationFolder = $this->getDestinationFolder($user, $rangeId);
        $copiedFolder = \FileManager::copyFolder(
            $sourceFolder->getTypedFolder(),
            $destinationFolder,
            $user
        );

        if (is_object($copiedFolder)) {
            return $folder_map[$folderId] = $copiedFolder->id;
        }

        return '';
    }

    private function getDestinationFolder(\User $user, string $rangeId): \StandardFolder
    {
        $rootFolder = \Folder::findTopFolder($rangeId);
        $destinationFolderName = 'Courseware Import '.date('d.m.Y', time());
        $destinationFolder = \Folder::findOneBySQL(
            'parent_id = ? AND name = ?',
            [$rootFolder->id, $destinationFolderName]
        );

        if (!$destinationFolder) {
            if ($user->id === $rangeId) {
                $folder_type = 'PublicFolder';
            } else {
                $folder_type = 'HiddenFolder';
            }
            $destinationFolder = \FileManager::createSubFolder(
                \FileManager::getTypedFolder($rootFolder->id),
                $user,
                $folder_type,
                $destinationFolderName,
                ''
            );
            if ($user->id !== $rangeId) {
                $destinationFolder->__set('download_allowed', 1);
            }
            $destinationFolder->store();
        } else {
            $destinationFolder = $destinationFolder->getTypedFolder();
        }

        return $destinationFolder;
    }

    /**
     * Gets the related block's html template if exists otherwise a default one, to be exported as pdf if exists.
     *
     * It turns the classname into snakecase in order to find the
     * template file in templates/courseware/block_types.
     *
     * @return mixed the \Flexi_Template instance if exists, otherwise null.
     */
    public function getPdfHtmlTemplate(): ?\Flexi_Template
    {
        $template = null;
        try {
            $template_name = strtosnakecase((new \ReflectionClass($this))->getShortName());
            $template_path = $GLOBALS['template_factory']->get_path() . "courseware/block_types/{$template_name}.php";
            if (file_exists($template_path)) {
                $template = $GLOBALS['template_factory']->open("courseware/block_types/{$template_name}");
            } else {
                $template = $GLOBALS['template_factory']->open("courseware/block_types/default");
            }
            $template->set_attributes([
                'title' => $this->getTitle(),
                'payload' => $this->getPayload(),
                'files' => $this->block->files
            ]);
        } catch (\Exception $e) {
            // it catches the exception mostly because the template file could not be found.
        }
        return $template;
    }
}
