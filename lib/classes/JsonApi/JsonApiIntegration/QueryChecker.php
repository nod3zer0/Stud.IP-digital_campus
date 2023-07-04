<?php

namespace JsonApi\JsonApiIntegration;

use Neomerx\JsonApi\Exceptions\JsonApiException;
use Neomerx\JsonApi\Schema\ErrorCollection;

class QueryChecker
{
    /**
     * @var bool
     */
    private $allowUnrecognized;

    /**
     * @var array|null
     */
    private $includePaths;

    /**
     * @var array|null
     */
    private $fieldSetTypes;

    /**
     * @var array|null
     */
    private $pagingParameters;

    /**
     * @var array|null
     */
    private $sortParameters;

    /**
     * @var array|null
     */
    private $filteringParameters;

    public function __construct(
        bool $allowUnrecognized = true,
        array $includePaths = null,
        array $fieldSetTypes = null,
        array $sortParameters = null,
        array $pagingParameters = null,
        array $filteringParameters = null
    ) {
        $this->includePaths = $includePaths;
        $this->allowUnrecognized = $allowUnrecognized;
        $this->fieldSetTypes = $fieldSetTypes;
        $this->sortParameters = $this->flip($sortParameters);
        $this->pagingParameters = $this->flip($pagingParameters);
        $this->filteringParameters = $this->flip($filteringParameters);
    }

    public function checkQuery(QueryParserInterface $queryParser): void
    {
        $errors = new ErrorCollection();

        $this->checkIncludePaths($errors, $queryParser);
        $this->checkFieldSets($errors, $queryParser);
        $this->checkFiltering($errors, $queryParser);
        $this->checkSorting($errors, $queryParser);
        $this->checkPaging($errors, $queryParser);
        $this->checkUnrecognized($errors, $queryParser);

        if ($errors->count()) {
            throw new JsonApiException($errors, JsonApiException::HTTP_CODE_BAD_REQUEST);
        }
    }

    protected function checkIncludePaths(ErrorCollection $errors, QueryParserInterface $queryParser): void
    {
        $invalidValues = $this->getInvalidValues(
            $queryParser->getIncludePaths(),
            $this->includePaths
        );
        foreach ($invalidValues as $value) {
            $errors->addQueryParameterError(
                QueryParser::PARAM_INCLUDE,
                sprintf('Include path %s is not allowed.', $value)
            );
        }
    }

    protected function checkFieldSets(ErrorCollection $errors, QueryParserInterface $queryParser): void
    {
        $invalidFields = $this->getInvalidFields($queryParser->getFields());
        foreach ($invalidFields as $field) {
            $errors->addQueryParameterError(
                QueryParser::PARAM_FIELDS,
                sprintf('Field set %s is not allowed.', $field)
            );
        }
    }

    protected function checkFiltering(ErrorCollection $errors, QueryParserInterface $queryParser): void
    {
        $invalidKeys = $this->getInvalidKeys(
            $queryParser->getFilters(),
            $this->filteringParameters
        );
        foreach ($invalidKeys as $key) {
            $errors->addQueryParameterError(
                QueryParser::PARAM_FILTER,
                sprintf('Filter parameter %s is not allowed.', $key)
            );
        }
    }

    protected function checkSorting(ErrorCollection $errors, QueryParserInterface $queryParser): void
    {
        $invalidKeys = $this->getInvalidKeys(
            $queryParser->getSorts(),
            $this->sortParameters
        );
        foreach ($invalidKeys as $key) {
            $errors->addQueryParameterError(
                QueryParser::PARAM_SORT,
                sprintf('Sort parameter %s is not allowed.', $key)
            );
        }
    }

    protected function checkPaging(ErrorCollection $errors, QueryParserInterface $queryParser): void
    {
        $invalidKeys = $this->getInvalidKeys(
            $queryParser->getPagination(),
            $this->pagingParameters
        );
        foreach ($invalidKeys as $key) {
            $errors->addQueryParameterError(
                QueryParser::PARAM_PAGE,
                sprintf('Page parameter %s is not allowed.', $key)
            );
        }
    }

    protected function checkUnrecognized(ErrorCollection $errors, QueryParserInterface $queryParser): void
    {
        if (!$this->allowUnrecognized && !empty($queryParser->getUnrecognizedParameters())) {
            foreach ($queryParser->getUnrecognizedParameters() as $name => $value) {
                $errors->addQueryParameterError($name, 'Unrecognized Parameter.');
            }
        }
    }

    private function getInvalidKeys(iterable $toCheck = null, iterable $allowed = null): array
    {
        if (null === $toCheck || null === $allowed) {
            return [];
        }

        return array_keys(array_diff_key(
            $this->ensureArray($toCheck),
            $this->ensureArray($allowed)
        ));
    }

    private function getInvalidValues(iterable $toCheck = null, iterable $allowed = null): array
    {
        if (null === $toCheck || null === $allowed) {
            return [];
        }

        return array_diff(
            $this->ensureArray($toCheck),
            $this->ensureArray($allowed)
        );
    }

    private function ensureArray(iterable $input): array
    {
        return is_array($input) ? $input : iterator_to_array($input);
    }

    private function flip(array $array = null): ?array
    {
        return $array === null ? null : array_flip($array);
    }

    /**
     * Check input fields against allowed.
     */
    private function getInvalidFields(iterable $fields = null): iterable
    {
        if ($this->fieldSetTypes !== null && $fields !== null) {
            foreach ($fields as $type => $requestedFields) {
                if (
                    !array_key_exists($type, $this->fieldSetTypes)
                    || (
                        // if not all fields are allowed and requested more fields than allowed
                        isset($this->fieldSetTypes[$type])
                        && !empty(array_diff(
                            $this->ensureArray($requestedFields),
                            $this->fieldSetTypes[$type]
                        ))
                    )
                ) {
                    yield $type;
                }
            }
        }
    }
}
