<?php

namespace Courseware\BlockTypes;

use Opis\JsonSchema\Schema;

/**
 * This class represents the content of a Courseware embed block.
 *
 * @author  Ron Lucke <lucke@elan-ev.de>
 * @license GPL2 or any later version
 *
 * @since   Stud.IP 5.0
 */
class Embed extends BlockType
{
    public static function getType(): string
    {
        return 'embed';
    }

    public static function getTitle(): string
    {
        return _('Embed');
    }

    public static function getDescription(): string
    {
        return _('Bindet externe Inhalte wie Videos, Grafiken oder Musik ein.');
    }

    public function initialPayload(): array
    {
        return [
            'title' => '',
            'url' => '',
            'source' => '',
            'starttime' => '', // for youtube
            'endtime' => '', // for youtube
        ];
    }

    public static function getJsonSchema(): Schema
    {
        $schemaFile = __DIR__.'/Embed.json';

        return Schema::fromJsonString(file_get_contents($schemaFile));
    }

    public function getPayload()
    {
        $payload = $this->decodePayloadString($this->block['payload']);

        $oembedRequest = $this->buildOembedRequest($payload['source'], $payload['url']);
        $payload['oembed_request'] = $oembedRequest;
        $payload['oembed-unauthorized'] = false;
        $payload['oembed-not-found'] = false;
        $payload['oembed'] = '';
        $payload['request'] = '';
        $request = \FileManager::fetchURLMetadata($oembedRequest);
        if ($request['response_code'] === 200) {
            $payload['request'] = file_get_contents($oembedRequest, false, get_default_http_stream_context($oembedRequest));
            $payload['oembed'] = json_decode($payload['request']);
        } elseif ($request['response_code'] === 401) {
            $payload['oembed_unauthorized'] = true;
        } else {
            $payload['oembed_not_found'] = true;
        }
        return $payload;
    }

    private function buildOembedRequest($source, $url)
    {
        $endPoints = [
            'audiomack' => 'https://www.audiomack.com/oembed',
            'codepen' => 'https://codepen.io/api/oembed',
            'codesandbox' => 'https://codesandbox.io/oembed',
            'deviantart' => 'https://backend.deviantart.com/oembed',
            'ethfiddle' => 'https://ethfiddle.com/services/oembed/',
            'flickr' => 'https://www.flickr.com/services/oembed/',
            'giphy' => 'https://giphy.com/services/oembed',
            'kidoju' => 'https://www.kidoju.com/api/oembed',
            'learningapps' => 'https://learningapps.org/oembed.php',
            'sketchfab' => 'https://sketchfab.com/oembed',
            'slideshare' => 'https://www.slideshare.net/api/oembed/2',
            'soundcloud' => 'https://soundcloud.com/oembed',
            'speakerdeck' => 'https://speakerdeck.com/oembed.json',
            'sway' => 'https://sway.com/api/v1.0/oembed',
            'sway.office' => 'https://sway.office.com/api/v1.0/oembed',
            'spotify' => 'https://embed.spotify.com/oembed/',
            'vimeo' => 'https://vimeo.com/api/oembed.json',
            'youtube' => 'https://www.youtube.com/oembed',
        ];

        return $endPoints[$source].'?url='.rawurlencode($url).'&format=json';
    }

    public static function getCategories(): array
    {
        return ['external'];
    }

    public static function getContentTypes(): array
    {
        return ['text', 'video', 'audio', 'image', 'rich'];
    }

    public static function getFileTypes(): array
    {
        return [];
    }

    public static function getTags(): array
    {
        return [
            _('Text'), _('Video'), _('Audio'), _('Bild'), _('Webseite'), 'YouTube',
            'Vimeo', 'Spotify', 'swan.office', 'speakerdeck', 'soundcloud',
            'slideshare', 'sketchfab', 'learningapps', 'kidoju', 'giphy',
            'flickr', 'ethfiddle', 'deviantart', 'codesandbox', 'codepen',
            'audiomack', _('extern'), _('einbinden'), _('einbetten'), _('einfügen'),
            _('Multimedia'), _('Grafik'), _('Podcast'), _('Medien'), _('bereitstellen'),
            'gif', 'meme', _('Cloud'), _('Interaktion')
        ];
    }
}
