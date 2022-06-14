<?php
namespace JsonApi\Routes\Consultations;

use JsonApi\Errors\BadRequestException;

trait FilterTrait
{
    private function validateFilters()
    {
        $filtering = $this->getQueryParameters()->getFilteringParameters() ?? [];

        if (array_key_exists('current', $filtering)) {
            if (!ctype_digit($filtering['current']) || !in_array($filtering['current'], [0, 1])) {
                throw new BadRequestException('Filter "current" may only be 0 or 1.');
            }
        }

        if (array_key_exists('expired', $filtering)) {
            if (!ctype_digit($filtering['expired']) || !in_array($filtering['expired'], [0, 1])) {
                throw new BadRequestException('Filter "expired" may only be 0 or 1.');
            }
        }
    }

    private function getFilters(): array
    {
        $filtering = $this->getQueryParameters()->getFilteringParameters() ?? [];

        $has_filter = isset($filtering['current']) || isset($filtering['expired']);

        $filters['current'] = $filtering['current'] ?? !$has_filter;
        $filters['expired'] = $filtering['expired'] ?? !$has_filter;

        return $filters;
    }
}
