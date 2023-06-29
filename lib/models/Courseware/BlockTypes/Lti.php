<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware LTI block.
 *
 * @author  Dennis Benz <debenz@uos.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.3
 */
class Lti extends BlockType
{
    public static function getType(): string
    {
        return 'lti';
    }

    public static function getTitle(): string
    {
        return _('LTI');
    }

    public static function getDescription(): string
    {
        return _('Einbinden eines externen Tools.');
    }

    public function initialPayload(): array
    {
        return [
            'title' => '',
            'height' => '640',
            'tool_id' => '',
            'launch_url' => '',
            'consumer_key' => '',
            'consumer_secret' => '',
            'oauth_signature_method' => 'sha1',
            'send_lis_person' => false,
            'custom_parameters' => '',
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Lti.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public function getPayload()
    {
        $payload = $this->decodePayloadString($this->block['payload']);
        $user = \User::findCurrent();

        // Remove sensitive lti parameters if user has no edit permission
        if (!$this->block->getStructuralElement()->canEdit($user)) {
            unset($payload['launch_url']);
            unset($payload['consumer_key']);
            unset($payload['consumer_secret']);
            unset($payload['oauth_signature_method']);
            unset($payload['send_lis_person']);
            unset($payload['custom_parameters']);
        }

        return $payload;
    }

    public static function getCategories(): array
    {
        return ['external'];
    }

    public static function getContentTypes(): array
    {
        return ['rich'];
    }

    public static function getFileTypes(): array
    {
        return [];
    }
}
