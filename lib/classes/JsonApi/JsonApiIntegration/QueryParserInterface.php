<?php

namespace JsonApi\JsonApiIntegration;

use Neomerx\JsonApi\Contracts\Http\Query\BaseQueryParserInterface;

interface QueryParserInterface extends BaseQueryParserInterface
{
    /** Query parameter */
    const PARAM_PAGING_LIMIT = 'limit';

    /** Query parameter */
    const PARAM_PAGING_OFFSET = 'offset';

    public function getIncludePaths(): iterable;

    public function getFilters(): iterable;

    /**
     * @return iterable
     */
    public function getPagination(): iterable;

    public function getUnrecognizedParameters(): iterable;
}
