<?php

namespace JsonApi\Routes\Holidays;

use GuzzleHttp\Psr7;
use JsonApi\JsonApiIntegration\QueryParserInterface;
use JsonApi\NonJsonApiController;
use Neomerx\JsonApi\Exceptions\JsonApiException;
use Neomerx\JsonApi\Schema\Error;
use Neomerx\JsonApi\Schema\ErrorCollection;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * List all holidays for a specific time period.
 *
 * Filter are allowed for year, month and days. You must specify a year in order
 * to filter by a month and you must specify a month in order to filter by a
 * day.
 *
 * If no filter is set, a filter the current year is assumed.
 */
final class HolidaysShow extends NonJsonApiController
{
    private $query_parser;

    protected $allowedFilteringParameters = ['year', 'month', 'day'];

    public function __construct(
        ContainerInterface $container,
        QueryParserInterface $queryParser
    ) {
        parent::__construct($container);

        $this->query_parser = $queryParser;
    }

    public function __invoke(Request $request, Response $response, $args): Response
    {
        [$current, $end] = $this->getTimespanByFilters();

        $holidays = [];
        while ($current < $end) {
            $holiday = holiday($current);
            if ($holiday) {
                $holidays[date('Y-m-d', $current)] = [
                    'holiday'   => $holiday['name'],
                    'mandatory' => $holiday['col'] === 3,
                ];
            }

            $current = strtotime('+1 day', $current);
        }

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withBody(Psr7\Utils::streamFor(json_encode($holidays)));
    }

    private function getTimespanByFilters(): array
    {
        $filters = $this->getFilters();

        $begin = mktime(
            0,
            0,
            0,
            $filters['month'] ?? 1,
            $filters['day'] ?? 1,
            $filters['year']
        );

        $end = mktime(
            23,
            59,
            59,
            $filters['month'] ?? 12,
            $filters['day'] ?? $this->getLastDayOfMonth($filters['year'], $filters['month'] ?? 12),
            $filters['year']
        );

        return [$begin, $end];
    }

    private function getLastDayOfMonth(int $year, int $month): int
    {
        $first_of_month = mktime(0, 0, 0, $month, 1, $year);
        $last_day_of_month = strtotime('last day of this month', $first_of_month);
        return (int) date('d', $last_day_of_month);
    }

    /**
     * @todo imporove error handling
     * @return array
     */
    private function getFilters(): array
    {
        $errors = new ErrorCollection();

        // Get filters
        $filters = $this->query_parser->getFilteringParameters();

        // Validate allowed filters
        foreach ($filters as $key => $value) {
            if (!in_array($key, $this->allowedFilteringParameters)) {
                $errors->add(new Error(
                    'invalid-filter-field',
                    null, null,
                    null, null,
                    'Filter should contain only allowed values.',
                    "Cannot filter by {$key}",
                    ['filter' => $key]
                ));
            }
        }

        // Validate month
        if (isset($filters['month']) && !isset($filters['year'])) {
            $errors->add(new Error(
                'missing-year-filter',
                null, null,
                null, null,
                'You must not define a month filter without a year filter'
            ));
        } elseif (
            isset($filters['month'])
            && ($filters['month'] < 1 || $filters['month'] > 12)
        ) {
            $errors->add(new Error(
                'invalid-filter-value',
                null, null,
                null, null,
                'Filter should contain only allowed values.',
                "Invalid value {$filters['month']} for month filter",
                ['filter' => 'month', 'value' => $filters['month']]
            ));
        }

        // Validate day
        if (isset($filters['day']) && !isset($filters['month'])) {
            $errors->add(new Error(
                'missing-month-filter',
                null, null,
                null, null,
                'You must not define a day filter without a month filter'
            ));
        } elseif (
            isset($filters['day'])
            && (
                $filters['day'] < 1
                || $filters['day'] > $this->getLastDayOfMonth((int) $filters['year'] ?? date('Y'), $filters['month'])
            )
        ) {
            $errors->add(new Error(
                'invalid-filter-value',
                null, null,
                null, null,
                'Filter should contain only allowed values.',
                "Invalid value {$filters['day']} for day filter",
                ['filter' => 'day', 'value' => $filters['day']]
            ));
        }

        if ($errors->count() > 0) {
            throw new JsonApiException($errors, JsonApiException::HTTP_CODE_BAD_REQUEST);
        }

        // Apply defaults
        return array_merge(['year' => date('Y')], $filters);
    }
}
