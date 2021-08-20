<?php

namespace JsonApi\JsonApiIntegration;

use Neomerx\JsonApi\Contracts\Http\Query\BaseQueryParserInterface as P;
use Neomerx\JsonApi\Http\Query\BaseQueryParser;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use Neomerx\JsonApi\Schema\Error;

/**
 */
class QueryParser extends BaseQueryParser implements QueryParserInterface
{
    public function getFilteringParameters(): array
    {
        return iterator_to_array($this->getFilters());
    }

    public function getFilters(): iterable
    {
        $parameters = $this->getParameters();
        if (array_key_exists(P::PARAM_FILTER, $parameters)) {
            $filters = $parameters[P::PARAM_FILTER];
            if (!is_array($filters) || empty($filters)) {
                $errorTitle = $this->getMessage(static::MSG_ERR_INVALID_PARAMETER);
                $error = new Error(null, null, null, null, null, $errorTitle, null, [
                    Error::SOURCE_PARAMETER => P::PARAM_FILTER,
                ]);
                throw new JsonApiException($error);
            }

            foreach ($filters as $type => $field) {
                yield $type => $field;
            }
        }
    }

    /**
     * Get pagination parameters from encoder.
     *
     * @return iterable
     */
    public function getPagination(): iterable
    {
        $parameters = $this->getParameters();
        if (array_key_exists(P::PARAM_PAGE, $parameters)) {
            $pagination = $parameters[P::PARAM_PAGE];
            if (!is_array($pagination) || empty($pagination)) {
                $errorTitle = $this->getMessage(static::MSG_ERR_INVALID_PARAMETER);
                $error = new Error(null, null, null, null, null, $errorTitle, null, [
                    Error::SOURCE_PARAMETER => P::PARAM_PAGE,
                ]);
                throw new JsonApiException($error);
            }

            foreach ($pagination as $type => $field) {
                yield $type => $field;
            }
        }
    }

    public function getUnrecognizedParameters(): iterable
    {
        $parameters = $this->getParameters();
        $supported = [
            P::PARAM_INCLUDE => 0,
            P::PARAM_FIELDS  => 0,
            P::PARAM_PAGE    => 0,
            P::PARAM_FILTER  => 0,
            P::PARAM_SORT    => 0,
        ];
        $unrecognized = array_diff_key($parameters, $supported);

        return $unrecognized;
    }
}
