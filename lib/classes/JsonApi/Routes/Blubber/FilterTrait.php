<?php

namespace JsonApi\Routes\Blubber;

use JsonApi\Errors\BadRequestException;

trait FilterTrait
{
    private function validateFilters()
    {
        $filtering = $this->getQueryParameters()->getFilteringParameters() ?? [];

        if (array_key_exists('since', $filtering)) {
            if (!self::isValidTimestamp($filtering['since'])) {
                throw new BadRequestException('Invalid ISP 8601 timestamp.');
            }
        }

        if (array_key_exists('before', $filtering)) {
            if (!self::isValidTimestamp($filtering['before'])) {
                throw new BadRequestException('Invalid ISP 8601 timestamp.');
            }
        }
    }

    private function getFilters(): array
    {
        $filtering = $this->getQueryParameters()->getFilteringParameters() ?? [];

        $filters['since'] = isset($filtering['since']) ? self::fromISO8601($filtering['since'])->getTimestamp() : null;
        $filters['before'] = isset($filtering['before']) ? self::fromISO8601($filtering['before'])->getTimestamp() : null;
        $filters['search'] = $filtering['search'] ?? null;

        return $filters;
    }
}
