<?php

namespace JsonApi\Routes\Blubber;

use JsonApi\Errors\BadRequestException;

trait SortTrait
{
    private function getSortParameters(): array
    {
        $sortParameters = iterator_to_array($this->getQueryParameters()->getSorts()) ?? [];

        return $sortParameters;
    }
}
