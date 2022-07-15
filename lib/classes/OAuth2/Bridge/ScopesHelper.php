<?php

namespace Studip\OAuth2\Bridge;

trait ScopesHelper
{
    public function formatScopes(array $scopes): string
    {
        return json_encode($this->scopesToArray($scopes));
    }

    public function scopesToArray(array $scopes): array
    {
        return array_map(function ($scope) {
            return $scope->getIdentifier();
        }, $scopes);
    }
}
