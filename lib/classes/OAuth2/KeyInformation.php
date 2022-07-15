<?php

namespace Studip\OAuth2;

class KeyInformation
{
    /** @var string */
    private $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function exists(): bool
    {
        return file_exists($this->filename);
    }

    public function isReadable(): bool
    {
        return is_readable($this->filename);
    }

    public function hasProperMode(): bool
    {
        return $this->mode() === '600' ||  $this->mode() === '660';
    }

    public function mode(): string
    {
        $result = '';
        if ($this->isReadable()) {
            $stat = stat($this->filename);
            if ($stat !== false) {
                $result = substr(sprintf('%o', $stat['mode']), -3);
            }
        }

        return $result;
    }
}
